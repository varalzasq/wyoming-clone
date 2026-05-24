import express from 'express';
import { getStakingYield } from '../controllers/staking.controller';
import { authenticateToken } from '../middleware/auth';

const router = express.Router();
router.use(authenticateToken);

router.get('/yield', getStakingYield);

export default router;
