from datetime import datetime, timezone
from typing import Dict, Any, Optional, Set
import jwt

class ServiceJWTVerifier:
    """Asymmetric JWT Verifier for inter-service communication (§17.6, DEC-030)."""

    def __init__(self, public_keys: Dict[str, str], expected_audience: str):
        self.public_keys = public_keys  # {"core": pem_string, "connector": pem_string}
        self.expected_audience = expected_audience
        self._seen_jtis: Set[str] = set()

    def verify_token(self, token: str) -> Dict[str, Any]:
        unverified_headers = jwt.get_unverified_header(token)
        alg = unverified_headers.get("alg")
        if alg not in ["RS256", "EdDSA"]:
            raise ValueError(f"Unsupported algorithm: {alg}")

        unverified_payload = jwt.decode(token, options={"verify_signature": False})
        issuer = unverified_payload.get("iss")
        if issuer not in self.public_keys:
            raise ValueError(f"Unknown issuer: {issuer}")

        public_key = self.public_keys[issuer]

        decoded = jwt.decode(
            token,
            public_key,
            algorithms=["RS256", "EdDSA"],
            audience=self.expected_audience,
            options={"require": ["exp", "iss", "aud", "jti", "sub"]}
        )

        jti = decoded["jti"]
        if jti in self._seen_jtis:
            raise ValueError(f"JTI replay detected: {jti}")
        self._seen_jtis.add(jti)

        return decoded
