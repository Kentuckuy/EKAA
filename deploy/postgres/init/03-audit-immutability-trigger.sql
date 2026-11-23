-- =============================================================================
-- EKAA Audit Log Tamper-Proof Immutability Trigger (AC-M8-03, §10, §30.4)
-- Enforces absolute append-only behavior: raises exception on UPDATE or DELETE
-- =============================================================================

CREATE OR REPLACE FUNCTION core.prevent_audit_log_modification()
RETURNS TRIGGER AS $$
BEGIN
    RAISE EXCEPTION 'PERMISSION_DENIED: Log audit bersifat append-only permanen dan tidak boleh diubah atau dihapus.'
        USING ERRCODE = '55000'; -- object_not_in_prerequisite_state
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_immutable_audit_logs ON core.audit_logs;

CREATE TRIGGER trg_immutable_audit_logs
BEFORE UPDATE OR DELETE ON core.audit_logs
FOR EACH ROW
EXECUTE FUNCTION core.prevent_audit_log_modification();
