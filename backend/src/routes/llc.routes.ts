import { Router } from 'express';
import { registerLlc, contactUs, dispatchWallet } from '../controllers/llc.controller';

const router = Router();

// Public Routes
router.post('/register-llc', registerLlc);
router.post('/contact', contactUs);

// Admin Routes
router.post('/admin/dispatch-wallet', dispatchWallet);

export default router;
