"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.mockAdminVerify = exports.mockAdminLogin = void 0;
const crypto_1 = require("crypto");
function verifyPassword(plain, stored) {
    const [salt, hash] = stored.split(":");
    if (!salt || !hash)
        return false;
    const attempt = (0, crypto_1.pbkdf2Sync)(plain, salt, 210000, 64, "sha512").toString("hex");
    try {
        return (0, crypto_1.timingSafeEqual)(Buffer.from(attempt, "hex"), Buffer.from(hash, "hex"));
    }
    catch {
        return false;
    }
}
function signToken(payload, secret) {
    const header = Buffer.from(JSON.stringify({ alg: "HS256", typ: "JWT" })).toString("base64url");
    const body = Buffer.from(JSON.stringify({ ...payload, exp: Math.floor(Date.now() / 1000) + 8 * 3600 })).toString("base64url");
    const sig = (0, crypto_1.createHmac)("sha256", secret).update(`${header}.${body}`).digest("base64url");
    return `${header}.${body}.${sig}`;
}
const mockAdminLogin = async (req, res) => {
    const secret = process.env.ADMIN_JWT_SECRET;
    console.log("SECRET PRESENT?", !!secret);
    console.log("ADMINS PRESENT?", !!process.env.ADMIN_1_USER);
    if (!secret) {
        res.status(500).json({ success: false, error: "no secret" });
        return;
    }
    const { username, password } = req.body || {};
    // Load admin list from env
    const admins = [
        { u: process.env.ADMIN_1_USER, h: process.env.ADMIN_1_PASS, d: process.env.ADMIN_1_NAME },
        { u: process.env.ADMIN_2_USER, h: process.env.ADMIN_2_PASS, d: process.env.ADMIN_2_NAME },
    ];
    const match = admins.find(a => a.u && a.u === username);
    const hashToCheck = match?.h ?? "0000000000000000:0000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000";
    const valid = match ? verifyPassword(password ?? "", hashToCheck) : false;
    if (!valid || !match) {
        res.status(401).json({ success: false, message: "Invalid credentials." });
        return;
    }
    const token = signToken({ displayName: match.d, role: "admin" }, secret);
    res.status(200).json({ success: true, token, displayName: match.d });
};
exports.mockAdminLogin = mockAdminLogin;
const mockAdminVerify = async (req, res) => {
    const secret = process.env.ADMIN_JWT_SECRET;
    if (!secret) {
        res.status(500).json({ success: false });
        return;
    }
    const { token } = req.body || {};
    if (!token) {
        res.status(401).json({ valid: false });
        return;
    }
    const parts = token.split(".");
    if (parts.length !== 3) {
        res.status(401).json({ valid: false });
        return;
    }
    const [header, body, sig] = parts;
    const expectedSig = (0, crypto_1.createHmac)("sha256", secret).update(`${header}.${body}`).digest("base64url");
    try {
        if (!(0, crypto_1.timingSafeEqual)(Buffer.from(sig, "base64url"), Buffer.from(expectedSig, "base64url"))) {
            res.status(401).json({ valid: false });
            return;
        }
        const payload = JSON.parse(Buffer.from(body, "base64url").toString("utf-8"));
        if (payload.exp && payload.exp < Math.floor(Date.now() / 1000)) {
            res.status(401).json({ valid: false });
            return;
        }
        res.status(200).json({ valid: true, displayName: payload.displayName });
    }
    catch {
        res.status(401).json({ valid: false });
    }
};
exports.mockAdminVerify = mockAdminVerify;
