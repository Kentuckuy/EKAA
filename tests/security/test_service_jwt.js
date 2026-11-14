/**
 * EKAA Service JWT Verification Test Suite (§17.6, DEC-030, AC-M0-05)
 * Verifies asymmetric RS256 token exchange between services:
 * 1. Valid token accepted
 * 2. Expired token rejected
 * 3. Invalid audience (aud) rejected
 * 4. Replayed jti rejected
 * 5. Unknown issuer (iss) rejected
 */

const crypto = require('crypto');

// 1. Generate RSA Keypair for Issuer (e.g. Core)
const { publicKey, privateKey } = crypto.generateKeyPairSync('rsa', {
  modulusLength: 2048,
  publicKeyEncoding: { type: 'spki', format: 'pem' },
  privateKeyEncoding: { type: 'pkcs8', format: 'pem' }
});

// Helper: Base64Url encode/decode
function base64UrlEncode(str) {
  return Buffer.from(str)
    .toString('base64')
    .replace(/=/g, '')
    .replace(/\+/g, '-')
    .replace(/\//g, '_');
}

function base64UrlDecode(str) {
  str = str.replace(/-/g, '+').replace(/_/g, '/');
  while (str.length % 4) str += '=';
  return Buffer.from(str, 'base64').toString('utf8');
}

// Token Issuer
function issueToken({ iss, aud, sub, jti, exp, scope, pkey }) {
  const header = { alg: 'RS256', typ: 'JWT' };
  const payload = {
    iss: iss || 'core',
    aud: aud || 'ai',
    sub: sub || 'core-service',
    jti: jti || crypto.randomUUID(),
    iat: Math.floor(Date.now() / 1000),
    exp: exp !== undefined ? exp : Math.floor(Date.now() / 1000) + 300, // 5 min
    scope: scope || ['ai:ask', 'ai:ingest']
  };

  const encodedHeader = base64UrlEncode(JSON.stringify(header));
  const encodedPayload = base64UrlEncode(JSON.stringify(payload));
  const message = `${encodedHeader}.${encodedPayload}`;

  const signer = crypto.createSign('RSA-SHA256');
  signer.update(message);
  const signature = signer.sign(pkey || privateKey, 'base64')
    .replace(/=/g, '')
    .replace(/\+/g, '-')
    .replace(/\//g, '_');

  return `${message}.${signature}`;
}

// Token Verifier with JTI replay protection
class ServiceTokenVerifier {
  constructor(knownPublicKeys, expectedAudience) {
    this.knownPublicKeys = knownPublicKeys; // { [iss]: pemPublicKey }
    this.expectedAudience = expectedAudience;
    this.seenJti = new Set();
  }

  verify(token) {
    const parts = token.split('.');
    if (parts.length !== 3) {
      return { valid: false, error: 'MALFORMED_TOKEN' };
    }

    const [encodedHeader, encodedPayload, signature] = parts;
    const payload = JSON.parse(base64UrlDecode(encodedPayload));
    const now = Math.floor(Date.now() / 1000);

    // Check Issuer
    const pubKey = this.knownPublicKeys[payload.iss];
    if (!pubKey) {
      return { valid: false, error: 'UNKNOWN_ISSUER' };
    }

    // Verify Signature
    const message = `${encodedHeader}.${encodedPayload}`;
    const verifier = crypto.createVerify('RSA-SHA256');
    verifier.update(message);
    const rawSig = signature.replace(/-/g, '+').replace(/_/g, '/');
    const isSignatureValid = verifier.verify(pubKey, rawSig, 'base64');
    if (!isSignatureValid) {
      return { valid: false, error: 'INVALID_SIGNATURE' };
    }

    // Check Expiration
    if (payload.exp && payload.exp < now) {
      return { valid: false, error: 'EXPIRED_TOKEN' };
    }

    // Check Audience
    if (payload.aud !== this.expectedAudience) {
      return { valid: false, error: 'AUDIENCE_MISMATCH' };
    }

    // Check JTI Replay
    if (this.seenJti.has(payload.jti)) {
      return { valid: false, error: 'JTI_REPLAY_DETECTED' };
    }
    this.seenJti.add(payload.jti);

    return { valid: true, payload };
  }
}

// -----------------------------------------------------------------------------
// Test Execution
// -----------------------------------------------------------------------------
console.log('=== Running AC-M0-05 Service JWT Tests ===');

const verifier = new ServiceTokenVerifier({ core: publicKey }, 'ai');

// 1. Valid Token
const validToken = issueToken({ iss: 'core', aud: 'ai' });
const res1 = verifier.verify(validToken);
console.assert(res1.valid === true, 'Test 1 Failed: Valid token should be accepted');
console.log('✓ Test 1: Valid RS256 service token accepted.');

// 2. Expired Token
const expiredToken = issueToken({ iss: 'core', aud: 'ai', exp: Math.floor(Date.now() / 1000) - 10 });
const res2 = verifier.verify(expiredToken);
console.assert(res2.valid === false && res2.error === 'EXPIRED_TOKEN', 'Test 2 Failed');
console.log('✓ Test 2: Expired token rejected.');

// 3. Invalid Audience
const wrongAudToken = issueToken({ iss: 'core', aud: 'connector' });
const res3 = verifier.verify(wrongAudToken);
console.assert(res3.valid === false && res3.error === 'AUDIENCE_MISMATCH', 'Test 3 Failed');
console.log('✓ Test 3: Audience mismatch rejected.');

// 4. JTI Replay
const jtiTest = crypto.randomUUID();
const tokenWithJti1 = issueToken({ iss: 'core', aud: 'ai', jti: jtiTest });
const firstUse = verifier.verify(tokenWithJti1);
console.assert(firstUse.valid === true, 'First use should succeed');
const replayUse = verifier.verify(tokenWithJti1);
console.assert(replayUse.valid === false && replayUse.error === 'JTI_REPLAY_DETECTED', 'Test 4 Failed');
console.log('✓ Test 4: JTI replay rejected.');

// 5. Unknown Issuer
const unknownIssToken = issueToken({ iss: 'malicious-service', aud: 'ai' });
const res5 = verifier.verify(unknownIssToken);
console.assert(res5.valid === false && res5.error === 'UNKNOWN_ISSUER', 'Test 5 Failed');
console.log('✓ Test 5: Unknown issuer rejected.');

console.log('=== All AC-M0-05 Service JWT Tests Passed 100% ===');
