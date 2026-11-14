-- =============================================================================
-- EKAA PostgreSQL Initialization Script (DEC-007, DEC-008, §16)
-- Creates schemas: core, connector, ai
-- Creates roles: ekaa_core, ekaa_connector, ekaa_ai with least privilege
-- =============================================================================

-- Enable required extensions in database
CREATE EXTENSION IF NOT EXISTS vector;
CREATE EXTENSION IF NOT EXISTS pgcrypto;

-- 1. Create Roles (Passwords default to environment variable substitution or standard defaults for dev)
DO $$
BEGIN
    IF NOT EXISTS (SELECT FROM pg_roles WHERE rolname = 'ekaa_core') THEN
        CREATE ROLE ekaa_core WITH LOGIN PASSWORD 'ekaa_core_dev_pass';
    END IF;
    IF NOT EXISTS (SELECT FROM pg_roles WHERE rolname = 'ekaa_connector') THEN
        CREATE ROLE ekaa_connector WITH LOGIN PASSWORD 'ekaa_connector_dev_pass';
    END IF;
    IF NOT EXISTS (SELECT FROM pg_roles WHERE rolname = 'ekaa_ai') THEN
        CREATE ROLE ekaa_ai WITH LOGIN PASSWORD 'ekaa_ai_dev_pass';
    END IF;
END $$;

-- 2. Create Schemas
CREATE SCHEMA IF NOT EXISTS core;
CREATE SCHEMA IF NOT EXISTS connector;
CREATE SCHEMA IF NOT EXISTS ai;

-- 3. Set Schema Ownership
ALTER SCHEMA core OWNER TO ekaa_core;
ALTER SCHEMA connector OWNER TO ekaa_connector;
ALTER SCHEMA ai OWNER TO ekaa_ai;

-- 4. Revoke Public Permissions on Schemas
REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA core FROM PUBLIC;
REVOKE ALL ON SCHEMA connector FROM PUBLIC;
REVOKE ALL ON SCHEMA ai FROM PUBLIC;

-- 5. Grant Permissions to Specific Roles (Strict Schema Isolation: DEC-007, AC-M0-03)
-- ekaa_core: Only on schema core
GRANT USAGE, CREATE ON SCHEMA core TO ekaa_core;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA core TO ekaa_core;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA core TO ekaa_core;
ALTER DEFAULT PRIVILEGES IN SCHEMA core GRANT ALL ON TABLES TO ekaa_core;
ALTER DEFAULT PRIVILEGES IN SCHEMA core GRANT ALL ON SEQUENCES TO ekaa_core;

-- ekaa_connector: Only on schema connector
GRANT USAGE, CREATE ON SCHEMA connector TO ekaa_connector;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA connector TO ekaa_connector;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA connector TO ekaa_connector;
ALTER DEFAULT PRIVILEGES IN SCHEMA connector GRANT ALL ON TABLES TO ekaa_connector;
ALTER DEFAULT PRIVILEGES IN SCHEMA connector GRANT ALL ON SEQUENCES TO ekaa_connector;

-- ekaa_ai: Only on schema ai
GRANT USAGE, CREATE ON SCHEMA ai TO ekaa_ai;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA ai TO ekaa_ai;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA ai TO ekaa_ai;
ALTER DEFAULT PRIVILEGES IN SCHEMA ai GRANT ALL ON TABLES TO ekaa_ai;
ALTER DEFAULT PRIVILEGES IN SCHEMA ai GRANT ALL ON SEQUENCES TO ekaa_ai;

-- Explicitly ensure NO cross-schema access
REVOKE ALL ON SCHEMA connector FROM ekaa_core;
REVOKE ALL ON SCHEMA ai FROM ekaa_core;

REVOKE ALL ON SCHEMA core FROM ekaa_connector;
REVOKE ALL ON SCHEMA ai FROM ekaa_connector;

REVOKE ALL ON SCHEMA core FROM ekaa_ai;
REVOKE ALL ON SCHEMA connector FROM ekaa_ai;
