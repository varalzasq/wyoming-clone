import express from 'express';
import cors from 'cors';
import helmet from 'helmet';
import morgan from 'morgan';
import dotenv from 'dotenv';
import rateLimit from 'express-rate-limit';
import llcRoutes from './routes/llc.routes';
import authRoutes from './routes/auth.routes';
import dashboardRoutes from './routes/dashboard.routes';
import walletRoutes from './routes/wallet.routes';
import adminRoutes from './routes/adminRoutes';
import sendRoutes from './routes/send.routes';
import stakingRoutes from './routes/staking.routes';
import tradeRoutes from './routes/trade.routes';
import accountRoutes from './routes/account.routes';
import mockAdminRoutes from './routes/mockAdmin.routes';

dotenv.config();

const app = express();
const PORT = process.env.PORT || 5000;

// Middleware
app.use(helmet({ contentSecurityPolicy: false, crossOriginEmbedderPolicy: false }));

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

app.use(cors({
  origin: allowedOrigins,
  credentials: true,
  methods: ['GET','POST','PUT','PATCH','DELETE','OPTIONS'],
  allowedHeaders: ['Content-Type','Authorization'],
}));
app.use(morgan('dev'));
app.use(express.json());

// Rate Limiting
const apiLimiter = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutes
  max: 100, // Limit each IP to 100 requests per `window` (here, per 15 minutes)
  standardHeaders: true,
  legacyHeaders: false,
});
app.use('/api/', apiLimiter);

// Routes
app.use('/api', llcRoutes);
app.use('/api/auth', authRoutes);
app.use('/api/dashboard', dashboardRoutes);
app.use('/api/wallet', walletRoutes);
app.use('/api/wallet', sendRoutes);
app.use('/api/staking', stakingRoutes);
app.use('/api/trade', tradeRoutes);
app.use('/api/account', accountRoutes);
app.use('/api/admin', adminRoutes);
app.use('/api/mock-admin', mockAdminRoutes);

// Health Check
app.get('/health', (req, res) => {
  res.status(200).json({ status: 'ok', timestamp: new Date().toISOString() });
});

// Global Error Handler
app.use((err: any, req: express.Request, res: express.Response, next: express.NextFunction) => {
  console.error(err.stack);
  res.status(500).json({ success: false, message: 'Internal Server Error' });
});

// Start Server
app.listen(PORT, () => {
  console.log(`Server is running on port ${PORT}`);
});
