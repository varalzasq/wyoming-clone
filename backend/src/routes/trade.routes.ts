import express from 'express';
import { processSwap, getSwapQuote } from '../controllers/trade.controller';
import { authenticateToken } from '../middleware/auth';

const router = express.Router();
router.use(authenticateToken);

router.get('/quote', getSwapQuote);
router.post('/swap', processSwap);

export default router;
