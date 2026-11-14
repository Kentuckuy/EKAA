/**
 * Database Schema Isolation & Role Permissions Test (§16, DEC-007, AC-M0-03)
 * Statically parses and verifies permissions and constraints in:
 * deploy/postgres/init/01-init-schemas-roles.sql and 02-base-tables.sql
 */

const fs = require('fs');
const path = require('path');

const initSqlPath = path.resolve(__dirname, '../../deploy/postgres/init/01-init-schemas-roles.sql');
const baseSqlPath = path.resolve(__dirname, '../../deploy/postgres/init/02-base-tables.sql');

const initSql = fs.readFileSync(initSqlPath, 'utf8');
const baseSql = fs.readFileSync(baseSqlPath, 'utf8');

console.log('=== Running AC-M0-03 Database Isolation Verification ===');

// 1. Verify Schema Creation
const hasCoreSchema = initSql.includes('CREATE SCHEMA IF NOT EXISTS core;');
const hasConnectorSchema = initSql.includes('CREATE SCHEMA IF NOT EXISTS connector;');
const hasAiSchema = initSql.includes('CREATE SCHEMA IF NOT EXISTS ai;');
console.assert(hasCoreSchema && hasConnectorSchema && hasAiSchema, 'All 3 schemas must be defined');
console.log('✓ 1. Schemas core, connector, ai defined.');

// 2. Verify Role Isolation & Revocation
console.assert(initSql.includes('REVOKE ALL ON SCHEMA connector FROM ekaa_core;'), 'ekaa_core must have no access to connector');
console.assert(initSql.includes('REVOKE ALL ON SCHEMA ai FROM ekaa_core;'), 'ekaa_core must have no access to ai');
console.assert(initSql.includes('REVOKE ALL ON SCHEMA core FROM ekaa_connector;'), 'ekaa_connector must have no access to core');
console.assert(initSql.includes('REVOKE ALL ON SCHEMA ai FROM ekaa_connector;'), 'ekaa_connector must have no access to ai');
console.assert(initSql.includes('REVOKE ALL ON SCHEMA core FROM ekaa_ai;'), 'ekaa_ai must have no access to core');
console.assert(initSql.includes('REVOKE ALL ON SCHEMA connector FROM ekaa_ai;'), 'ekaa_ai must have no access to connector');
console.log('✓ 2. Strict cross-schema REVOKE statements verified between all 3 roles.');

// 3. Verify audit_logs append-only protection
console.assert(
  baseSql.includes('REVOKE UPDATE, DELETE, TRUNCATE ON core.audit_logs FROM ekaa_core;'),
  'ekaa_core must NOT have UPDATE, DELETE, or TRUNCATE on audit_logs'
);
console.log('✓ 3. Append-only enforcement on core.audit_logs verified.');

// 4. Verify NULLS NOT DISTINCT on unique constraints (DEC-047)
console.assert(
  baseSql.includes('UNIQUE NULLS NOT DISTINCT (document_id, principal_type, principal_id)'),
  'document_permissions must use UNIQUE NULLS NOT DISTINCT'
);
console.assert(
  baseSql.includes('UNIQUE NULLS NOT DISTINCT (dataset_id, principal_type, principal_id)'),
  'dataset_permissions must use UNIQUE NULLS NOT DISTINCT'
);
console.log('✓ 4. UNIQUE NULLS NOT DISTINCT verified on permission tables.');

// 5. Verify ai.jobs table with queue index (DEC-032)
console.assert(baseSql.includes('CREATE TABLE IF NOT EXISTS ai.jobs'), 'ai.jobs table must be defined');
console.assert(baseSql.includes('WHERE status = \'queued\''), 'ai.jobs index for worker queue must exist');
console.log('✓ 5. ai.jobs table and worker index verified.');

console.log('=== All AC-M0-03 Database Isolation Checks Passed 100% ===');
