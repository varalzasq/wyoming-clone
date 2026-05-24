import { Router } from 'express';
import { getUserProfile, getLlcStats, getLlcList } from '../controllers/dashboard.controller';
import { authenticateToken } from '../middleware/auth';

const router = Router();

// Apply middleware to all dashboard routes
router.use(authenticateToken);

router.get('/user', getUserProfile);
router.get('/llc-stats', getLlcStats);
router.get('/llc-list', getLlcList);

export default router;
