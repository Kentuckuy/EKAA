-- =============================================================================
-- EKAA Base Schemas & Tables Definition (§16, DEC-007, DEC-026, DEC-047)
-- =============================================================================

-- -----------------------------------------------------------------------------
-- SCHEMA: core (Owned by ekaa_core)
-- -----------------------------------------------------------------------------
SET ROLE ekaa_core;

-- users
CREATE TABLE IF NOT EXISTS core.users (
    id UUID PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT true,
    must_change_password BOOLEAN NOT NULL DEFAULT false,
    password_changed_at TIMESTAMPTZ,
    last_login_at TIMESTAMPTZ,
    deleted_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX IF NOT EXISTS uq_users_email_lower ON core.users (LOWER(email)) WHERE deleted_at IS NULL;
CREATE INDEX IF NOT EXISTS idx_users_is_active ON core.users (is_active);

-- roles
CREATE TABLE IF NOT EXISTS core.roles (
    id UUID PRIMARY KEY,
    code VARCHAR(64) NOT NULL UNIQUE,
    name VARCHAR(128) NOT NULL,
    description TEXT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- permissions
CREATE TABLE IF NOT EXISTS core.permissions (
    id UUID PRIMARY KEY,
    code VARCHAR(128) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- role_permission
CREATE TABLE IF NOT EXISTS core.role_permission (
    role_id UUID NOT NULL REFERENCES core.roles(id) ON DELETE CASCADE,
    permission_id UUID NOT NULL REFERENCES core.permissions(id) ON DELETE CASCADE,
    PRIMARY KEY (role_id, permission_id)
);
CREATE INDEX IF NOT EXISTS idx_role_permission_perm ON core.role_permission(permission_id);

-- role_user
CREATE TABLE IF NOT EXISTS core.role_user (
    user_id UUID NOT NULL REFERENCES core.users(id) ON DELETE CASCADE,
    role_id UUID NOT NULL REFERENCES core.roles(id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, role_id)
);
CREATE INDEX IF NOT EXISTS idx_role_user_role ON core.role_user(role_id);

-- org_units (hierarchical units)
CREATE TABLE IF NOT EXISTS core.org_units (
    id UUID PRIMARY KEY,
    parent_id UUID REFERENCES core.org_units(id) ON DELETE RESTRICT,
    code VARCHAR(64) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_org_units_parent ON core.org_units(parent_id);

-- user_units
CREATE TABLE IF NOT EXISTS core.user_units (
    user_id UUID NOT NULL REFERENCES core.users(id) ON DELETE CASCADE,
    org_unit_id UUID NOT NULL REFERENCES core.org_units(id) ON DELETE RESTRICT,
    PRIMARY KEY (user_id, org_unit_id)
);
CREATE INDEX IF NOT EXISTS idx_user_units_unit ON core.user_units(org_unit_id);

-- data_sources
CREATE TABLE IF NOT EXISTS core.data_sources (
    id UUID PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    type VARCHAR(64) NOT NULL CHECK (type IN ('upload', 'folder', 'gdrive', 'sharepoint', 'database')),
    default_acl JSONB NOT NULL DEFAULT '[]'::jsonb,
    owner_id UUID NOT NULL REFERENCES core.users(id) ON DELETE RESTRICT,
    is_active BOOLEAN NOT NULL DEFAULT true,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- documents
CREATE TABLE IF NOT EXISTS core.documents (
    id UUID PRIMARY KEY,
    title VARCHAR(512) NOT NULL,
    owner_id UUID NOT NULL REFERENCES core.users(id) ON DELETE RESTRICT,
    data_source_id UUID NOT NULL REFERENCES core.data_sources(id) ON DELETE RESTRICT,
    status VARCHAR(32) NOT NULL DEFAULT 'draft' CHECK (status IN ('draft', 'active', 'revoked')),
    current_version_id UUID,
    external_ref VARCHAR(512),
    deleted_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_documents_status ON core.documents(status);
CREATE INDEX IF NOT EXISTS idx_documents_datasource ON core.documents(data_source_id);
CREATE UNIQUE INDEX IF NOT EXISTS uq_documents_source_extref ON core.documents(data_source_id, external_ref) WHERE external_ref IS NOT NULL;

-- document_versions
CREATE TABLE IF NOT EXISTS core.document_versions (
    id UUID PRIMARY KEY,
    document_id UUID NOT NULL REFERENCES core.documents(id) ON DELETE RESTRICT,
    version_no INTEGER NOT NULL,
    storage_key VARCHAR(1024) NOT NULL,
    mime_type VARCHAR(128) NOT NULL,
    size_bytes BIGINT NOT NULL,
    sha256 CHAR(64) NOT NULL,
    effective_date DATE,
    status VARCHAR(32) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'indexed', 'failed', 'superseded', 'revoked')),
    indexed_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_doc_version UNIQUE (document_id, version_no)
);
CREATE INDEX IF NOT EXISTS idx_doc_versions_sha256 ON core.document_versions(sha256);
CREATE INDEX IF NOT EXISTS idx_doc_versions_status ON core.document_versions(status);

-- document_permissions (DEC-047: UNIQUE NULLS NOT DISTINCT)
CREATE TABLE IF NOT EXISTS core.document_permissions (
    id UUID PRIMARY KEY,
    document_id UUID NOT NULL REFERENCES core.documents(id) ON DELETE CASCADE,
    principal_type VARCHAR(32) NOT NULL CHECK (principal_type IN ('role', 'unit', 'user', 'all')),
    principal_id UUID,
    access VARCHAR(32) NOT NULL DEFAULT 'read' CHECK (access IN ('read')),
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_doc_permissions UNIQUE NULLS NOT DISTINCT (document_id, principal_type, principal_id)
);
CREATE INDEX IF NOT EXISTS idx_doc_permissions_principal ON core.document_permissions(principal_type, principal_id);

-- ingestion_jobs
CREATE TABLE IF NOT EXISTS core.ingestion_jobs (
    id UUID PRIMARY KEY,
    document_version_id UUID NOT NULL REFERENCES core.document_versions(id) ON DELETE CASCADE,
    status VARCHAR(32) NOT NULL DEFAULT 'queued' CHECK (status IN ('queued', 'running', 'done', 'failed')),
    error TEXT,
    started_at TIMESTAMPTZ,
    finished_at TIMESTAMPTZ,
    attempt INTEGER NOT NULL DEFAULT 0,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_ingestion_jobs_status ON core.ingestion_jobs(status);
CREATE INDEX IF NOT EXISTS idx_ingestion_jobs_version ON core.ingestion_jobs(document_version_id);

-- datasets
CREATE TABLE IF NOT EXISTS core.datasets (
    id UUID PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    data_source_id UUID NOT NULL REFERENCES core.data_sources(id) ON DELETE RESTRICT,
    owner_id UUID NOT NULL REFERENCES core.users(id) ON DELETE RESTRICT,
    storage_key VARCHAR(1024) NOT NULL,
    row_count BIGINT NOT NULL DEFAULT 0,
    status VARCHAR(32) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'active', 'failed', 'archived')),
    schema_hash CHAR(64),
    locale_config JSONB NOT NULL DEFAULT '{"decimal_separator": ",", "thousand_separator": ".", "date_format": "DD/MM/YYYY"}'::jsonb,
    deleted_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- dataset_columns
CREATE TABLE IF NOT EXISTS core.dataset_columns (
    id UUID PRIMARY KEY,
    dataset_id UUID NOT NULL REFERENCES core.datasets(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    data_type VARCHAR(64) NOT NULL,
    unit VARCHAR(64),
    description TEXT,
    is_dimension BOOLEAN NOT NULL DEFAULT false,
    is_measure BOOLEAN NOT NULL DEFAULT false,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_dataset_column UNIQUE (dataset_id, name)
);

-- dataset_permissions (DEC-047: UNIQUE NULLS NOT DISTINCT)
CREATE TABLE IF NOT EXISTS core.dataset_permissions (
    id UUID PRIMARY KEY,
    dataset_id UUID NOT NULL REFERENCES core.datasets(id) ON DELETE CASCADE,
    principal_type VARCHAR(32) NOT NULL CHECK (principal_type IN ('role', 'unit', 'user', 'all')),
    principal_id UUID,
    access VARCHAR(32) NOT NULL DEFAULT 'read' CHECK (access IN ('read')),
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_dataset_permissions UNIQUE NULLS NOT DISTINCT (dataset_id, principal_type, principal_id)
);

-- metric_definitions
CREATE TABLE IF NOT EXISTS core.metric_definitions (
    id UUID PRIMARY KEY,
    code VARCHAR(64) NOT NULL UNIQUE,
    name VARCHAR(128) NOT NULL,
    description TEXT,
    function_ref VARCHAR(255) NOT NULL,
    params_schema JSONB NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT true,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- analytics_jobs
CREATE TABLE IF NOT EXISTS core.analytics_jobs (
    id UUID PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES core.users(id) ON DELETE CASCADE,
    dataset_id UUID NOT NULL REFERENCES core.datasets(id) ON DELETE CASCADE,
    metric_id UUID REFERENCES core.metric_definitions(id) ON DELETE SET NULL,
    params JSONB NOT NULL,
    result JSONB,
    formula TEXT,
    status VARCHAR(32) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'running', 'completed', 'failed')),
    duration_ms INTEGER,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_analytics_jobs_user_time ON core.analytics_jobs(user_id, created_at DESC);
CREATE INDEX IF NOT EXISTS idx_analytics_jobs_dataset ON core.analytics_jobs(dataset_id);

-- conversations
CREATE TABLE IF NOT EXISTS core.conversations (
    id UUID PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES core.users(id) ON DELETE CASCADE,
    title VARCHAR(512) NOT NULL,
    deleted_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_conversations_user_updated ON core.conversations(user_id, updated_at DESC);

-- messages
CREATE TABLE IF NOT EXISTS core.messages (
    id UUID PRIMARY KEY,
    conversation_id UUID NOT NULL REFERENCES core.conversations(id) ON DELETE CASCADE,
    role VARCHAR(32) NOT NULL CHECK (role IN ('user', 'assistant', 'system')),
    content TEXT NOT NULL,
    route VARCHAR(32) CHECK (route IN ('document', 'analytics', 'out_of_scope', 'clarification_required')),
    route_confidence NUMERIC(5, 4),
    payload JSONB,
    model_info JSONB,
    trace_id VARCHAR(128),
    latency_ms INTEGER,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_messages_conversation ON core.messages(conversation_id, created_at ASC);

-- message_citations
CREATE TABLE IF NOT EXISTS core.message_citations (
    id UUID PRIMARY KEY,
    message_id UUID NOT NULL REFERENCES core.messages(id) ON DELETE CASCADE,
    document_id UUID NOT NULL REFERENCES core.documents(id) ON DELETE CASCADE,
    version_id UUID NOT NULL REFERENCES core.document_versions(id) ON DELETE CASCADE,
    chunk_ref VARCHAR(128) NOT NULL,
    page_start INTEGER,
    page_end INTEGER,
    section_path TEXT,
    quote_excerpt VARCHAR(1024),
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_message_citations_msg ON core.message_citations(message_id);
CREATE INDEX IF NOT EXISTS idx_message_citations_doc ON core.message_citations(document_id);

-- feedback
CREATE TABLE IF NOT EXISTS core.feedback (
    id UUID PRIMARY KEY,
    message_id UUID NOT NULL REFERENCES core.messages(id) ON DELETE CASCADE,
    user_id UUID NOT NULL REFERENCES core.users(id) ON DELETE CASCADE,
    rating VARCHAR(32) NOT NULL CHECK (rating IN ('correct', 'incorrect')),
    note TEXT,
    reviewed_by UUID REFERENCES core.users(id) ON DELETE SET NULL,
    reviewed_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_feedback_message_user UNIQUE (message_id, user_id)
);

-- audit_logs (append-only, ekaa_core only has INSERT and SELECT)
CREATE TABLE IF NOT EXISTS core.audit_logs (
    id UUID PRIMARY KEY,
    occurred_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actor_id UUID,
    actor_type VARCHAR(64) NOT NULL DEFAULT 'user',
    action VARCHAR(128) NOT NULL,
    resource_type VARCHAR(64) NOT NULL,
    resource_id VARCHAR(128),
    outcome VARCHAR(32) NOT NULL DEFAULT 'success' CHECK (outcome IN ('success', 'failure', 'denied')),
    ip VARCHAR(45),
    correlation_id VARCHAR(128) NOT NULL,
    details JSONB
);
CREATE INDEX IF NOT EXISTS idx_audit_logs_occurred_at ON core.audit_logs(occurred_at);
CREATE INDEX IF NOT EXISTS idx_audit_logs_actor ON core.audit_logs(actor_id, occurred_at);
CREATE INDEX IF NOT EXISTS idx_audit_logs_action ON core.audit_logs(action);
CREATE INDEX IF NOT EXISTS idx_audit_logs_resource ON core.audit_logs(resource_type, resource_id);

-- Enforce append-only on audit_logs for ekaa_core: revoke UPDATE and DELETE
REVOKE UPDATE, DELETE, TRUNCATE ON core.audit_logs FROM ekaa_core;

-- system_settings
CREATE TABLE IF NOT EXISTS core.system_settings (
    key VARCHAR(128) PRIMARY KEY,
    value JSONB NOT NULL,
    is_secret BOOLEAN NOT NULL DEFAULT false,
    updated_by UUID REFERENCES core.users(id) ON DELETE SET NULL,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- refresh_tokens (DEC-031)
CREATE TABLE IF NOT EXISTS core.refresh_tokens (
    id UUID PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES core.users(id) ON DELETE CASCADE,
    token_hash VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMPTZ NOT NULL,
    revoked_at TIMESTAMPTZ,
    replaced_by VARCHAR(255),
    user_agent TEXT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_refresh_tokens_user ON core.refresh_tokens(user_id);

-- access_tokens (DEC-031: opaque access tokens)
CREATE TABLE IF NOT EXISTS core.access_tokens (
    id UUID PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES core.users(id) ON DELETE CASCADE,
    token_hash VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMPTZ NOT NULL,
    revoked_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_access_tokens_user ON core.access_tokens(user_id);


-- -----------------------------------------------------------------------------
-- SCHEMA: connector (Owned by ekaa_connector)
-- -----------------------------------------------------------------------------
RESET ROLE;
SET ROLE ekaa_connector;

CREATE TABLE IF NOT EXISTS connector.sources (
    id UUID PRIMARY KEY,
    core_source_id UUID NOT NULL UNIQUE,
    type VARCHAR(64) NOT NULL CHECK (type IN ('folder', 'sftp', 'smb')),
    config JSONB NOT NULL DEFAULT '{}'::jsonb,
    credentials_enc BYTEA,
    schedule VARCHAR(64) NOT NULL DEFAULT '0 * * * *',
    is_active BOOLEAN NOT NULL DEFAULT true,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS connector.sync_runs (
    id UUID PRIMARY KEY,
    source_id UUID NOT NULL REFERENCES connector.sources(id) ON DELETE CASCADE,
    started_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    finished_at TIMESTAMPTZ,
    status VARCHAR(32) NOT NULL DEFAULT 'running' CHECK (status IN ('running', 'completed', 'failed')),
    items_seen INTEGER NOT NULL DEFAULT 0,
    items_new INTEGER NOT NULL DEFAULT 0,
    items_changed INTEGER NOT NULL DEFAULT 0,
    error TEXT
);
CREATE INDEX IF NOT EXISTS idx_sync_runs_source ON connector.sync_runs(source_id, started_at DESC);

CREATE TABLE IF NOT EXISTS connector.source_items (
    id UUID PRIMARY KEY,
    source_id UUID NOT NULL REFERENCES connector.sources(id) ON DELETE CASCADE,
    external_id VARCHAR(512) NOT NULL,
    path TEXT NOT NULL,
    content_hash CHAR(64) NOT NULL,
    size_bytes BIGINT NOT NULL,
    last_seen_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    core_document_id UUID,
    state VARCHAR(32) NOT NULL DEFAULT 'new' CHECK (state IN ('new', 'synced', 'changed', 'removed', 'error')),
    idempotency_key VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_source_external UNIQUE (source_id, external_id)
);
CREATE INDEX IF NOT EXISTS idx_source_items_state ON connector.source_items(state);


-- -----------------------------------------------------------------------------
-- SCHEMA: ai (Owned by ekaa_ai)
-- -----------------------------------------------------------------------------
RESET ROLE;
SET ROLE ekaa_ai;

CREATE TABLE IF NOT EXISTS ai.embedding_models (
    id UUID PRIMARY KEY,
    name VARCHAR(128) NOT NULL UNIQUE,
    dimension INTEGER NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT true,
    config JSONB NOT NULL DEFAULT '{}'::jsonb,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS ai.document_index_state (
    document_id UUID NOT NULL,
    version_id UUID NOT NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'indexing', 'indexed', 'failed', 'revoked')),
    chunk_count INTEGER NOT NULL DEFAULT 0,
    embedding_model_id UUID REFERENCES ai.embedding_models(id) ON DELETE RESTRICT,
    indexed_at TIMESTAMPTZ,
    error TEXT,
    PRIMARY KEY (document_id, version_id)
);
CREATE INDEX IF NOT EXISTS idx_doc_index_status ON ai.document_index_state(status);

-- chunks table (DEC-008, DEC-025, DEC-047)
-- Dimension vector(1024) is initial placeholder, adjusted when model is locked in M3
CREATE TABLE IF NOT EXISTS ai.chunks (
    id UUID PRIMARY KEY,
    document_id UUID NOT NULL,
    version_id UUID NOT NULL,
    chunk_index INTEGER NOT NULL,
    parent_chunk_id UUID,
    content TEXT NOT NULL,
    content_tsv TSVECTOR GENERATED ALWAYS AS (to_tsvector('simple', content)) STORED,
    embedding VECTOR(1024),
    page_start INTEGER,
    page_end INTEGER,
    section_path TEXT,
    token_count INTEGER,
    acl_principals TEXT[] NOT NULL DEFAULT ARRAY['all']::TEXT[],
    status VARCHAR(32) NOT NULL DEFAULT 'active' CHECK (status IN ('active', 'revoked')),
    is_current BOOLEAN NOT NULL DEFAULT true,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_chunks_content_tsv ON ai.chunks USING GIN(content_tsv);
CREATE INDEX IF NOT EXISTS idx_chunks_acl_principals ON ai.chunks USING GIN(acl_principals);
CREATE INDEX IF NOT EXISTS idx_chunks_doc_ver ON ai.chunks(document_id, version_id);
CREATE INDEX IF NOT EXISTS idx_chunks_status_current ON ai.chunks(status, is_current);
-- HNSW Index with partial condition (DD-13)
CREATE INDEX IF NOT EXISTS idx_chunks_embedding_hnsw ON ai.chunks USING hnsw (embedding vector_cosine_ops)
    WHERE status = 'active' AND is_current = true;

-- jobs table for AI worker (DEC-032, DD-11: FOR UPDATE SKIP LOCKED)
CREATE TABLE IF NOT EXISTS ai.jobs (
    id UUID PRIMARY KEY,
    type VARCHAR(64) NOT NULL CHECK (type IN ('ingest', 'reindex', 'dataset_import')),
    payload JSONB NOT NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'queued' CHECK (status IN ('queued', 'running', 'done', 'failed')),
    attempts INTEGER NOT NULL DEFAULT 0,
    max_attempts INTEGER NOT NULL DEFAULT 3,
    run_after TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    locked_by VARCHAR(128),
    locked_at TIMESTAMPTZ,
    last_error TEXT,
    idempotency_key VARCHAR(255) NOT NULL UNIQUE,
    correlation_id VARCHAR(128),
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_ai_jobs_worker ON ai.jobs(status, run_after) WHERE status = 'queued';

CREATE TABLE IF NOT EXISTS ai.dataset_files (
    dataset_id UUID PRIMARY KEY,
    storage_key VARCHAR(1024) NOT NULL,
    duckdb_path VARCHAR(1024) NOT NULL,
    schema_hash CHAR(64) NOT NULL,
    row_count BIGINT NOT NULL DEFAULT 0,
    imported_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS ai.dataset_acl (
    dataset_id UUID NOT NULL,
    principal TEXT NOT NULL,
    PRIMARY KEY (dataset_id, principal)
);

CREATE TABLE IF NOT EXISTS ai.retrieval_traces (
    id UUID PRIMARY KEY,
    trace_id VARCHAR(128) NOT NULL,
    query_hash CHAR(64) NOT NULL,
    route VARCHAR(32) NOT NULL,
    candidates JSONB,
    reranked JSONB,
    latency_ms INTEGER,
    model_info JSONB,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_retrieval_traces_trace_id ON ai.retrieval_traces(trace_id);

-- Evaluation Tables (DEC-046, FR-035)
CREATE TABLE IF NOT EXISTS ai.eval_sets (
    id UUID PRIMARY KEY,
    name VARCHAR(128) NOT NULL,
    version VARCHAR(32) NOT NULL,
    description TEXT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_eval_sets_name_ver UNIQUE (name, version)
);

CREATE TABLE IF NOT EXISTS ai.eval_items (
    id UUID PRIMARY KEY,
    eval_set_id UUID NOT NULL REFERENCES ai.eval_sets(id) ON DELETE CASCADE,
    query TEXT NOT NULL,
    reference_answer TEXT,
    reference_sources JSONB,
    item_type VARCHAR(64) NOT NULL CHECK (item_type IN ('document', 'analytics', 'no_evidence', 'out_of_scope', 'access_leak', 'injection')),
    split VARCHAR(16) NOT NULL DEFAULT 'dev' CHECK (split IN ('dev', 'test')),
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_eval_items_set_split ON ai.eval_items(eval_set_id, split);

CREATE TABLE IF NOT EXISTS ai.eval_runs (
    id UUID PRIMARY KEY,
    eval_set_id UUID NOT NULL REFERENCES ai.eval_sets(id) ON DELETE CASCADE,
    run_name VARCHAR(128) NOT NULL,
    config JSONB NOT NULL,
    seed INTEGER NOT NULL DEFAULT 42,
    metrics JSONB NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS ai.eval_results (
    id UUID PRIMARY KEY,
    run_id UUID NOT NULL REFERENCES ai.eval_runs(id) ON DELETE CASCADE,
    eval_item_id UUID NOT NULL REFERENCES ai.eval_items(id) ON DELETE CASCADE,
    output JSONB NOT NULL,
    scores JSONB NOT NULL,
    passed BOOLEAN NOT NULL DEFAULT false,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_eval_results_run ON ai.eval_results(run_id);

RESET ROLE;
