"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const express_1 = __importDefault(require("express"));
const cors_1 = __importDefault(require("cors"));
const helmet_1 = __importDefault(require("helmet"));
const morgan_1 = __importDefault(require("morgan"));
const dotenv_1 = __importDefault(require("dotenv"));
const express_rate_limit_1 = __importDefault(require("express-rate-limit"));
const llc_routes_1 = __importDefault(require("./routes/llc.routes"));
const auth_routes_1 = __importDefault(require("./routes/auth.routes"));
const dashboard_routes_1 = __importDefault(require("./routes/dashboard.routes"));
const wallet_routes_1 = __importDefault(require("./routes/wallet.routes"));
const adminRoutes_1 = __importDefault(require("./routes/adminRoutes"));
const send_routes_1 = __importDefault(require("./routes/send.routes"));
const staking_routes_1 = __importDefault(require("./routes/staking.routes"));
const trade_routes_1 = __importDefault(require("./routes/trade.routes"));
const account_routes_1 = __importDefault(require("./routes/account.routes"));
const mockAdmin_routes_1 = __importDefault(require("./routes/mockAdmin.routes"));
dotenv_1.default.config();
const app = (0, express_1.default)();
const PORT = process.env.PORT || 5000;
// Middleware
app.use((0, helmet_1.default)({ contentSecurityPolicy: false, crossOriginEmbedderPolicy: false }));
let allowedOrigins = [
    'https://icapitalwyomingllc.com',
    'https://www.icapitalwyomingllc.com',
    'http://localhost:3000',
    'http://127.0.0.1:3000',
    'http://localhost:3001',
    'http://127.0.0.1:3001'
];
if (process.env.ALLOWED_ORIGINS) {
    allowedOrigins = allowedOrigins.concat(process.env.ALLOWED_ORIGINS.split(','));
}
if (process.env.CORS_ORIGIN) {
    allowedOrigins = allowedOrigins.concat(process.env.CORS_ORIGIN.split(','));
}
// Clean up any accidental spaces from env variables
allowedOrigins = allowedOrigins.map(origin => origin.trim());
app.use((0, cors_1.default)({
    origin: allowedOrigins,
    credentials: true,
    methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization'],
}));
app.use((0, morgan_1.default)('dev'));
app.use(express_1.default.json());
// Rate Limiting
const apiLimiter = (0, express_rate_limit_1.default)({
    windowMs: 15 * 60 * 1000, // 15 minutes
    max: 100, // Limit each IP to 100 requests per `window` (here, per 15 minutes)
    standardHeaders: true,
    legacyHeaders: false,
});
app.use('/api/', apiLimiter);
// Routes
app.use('/api', llc_routes_1.default);
app.use('/api/auth', auth_routes_1.default);
app.use('/api/dashboard', dashboard_routes_1.default);
app.use('/api/wallet', wallet_routes_1.default);
app.use('/api/wallet', send_routes_1.default);
app.use('/api/staking', staking_routes_1.default);
app.use('/api/trade', trade_routes_1.default);
app.use('/api/account', account_routes_1.default);
app.use('/api/admin', adminRoutes_1.default);
app.use('/api/mock-admin', mockAdmin_routes_1.default);
// Health Check
app.get('/health', (req, res) => {
    res.status(200).json({ status: 'ok', timestamp: new Date().toISOString() });
});
// Global Error Handler
app.use((err, req, res, next) => {
    console.error(err.stack);
    res.status(500).json({ success: false, message: 'Internal Server Error' });
});
// Start Server
app.listen(PORT, () => {
    console.log(`Server is running on port ${PORT}`);
});
