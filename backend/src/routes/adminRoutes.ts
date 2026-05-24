import { Router } from 'express';
import { requireAdmin } from '../middleware/auth';
import { getHotWalletStats, getPlatformLiability } from '../controllers/admin.controller';
import { getAllUsers, updateUserBalance } from '../controllers/adminUsers.controller';

const router = Router();

// Protect all admin routes with requireAdmin middleware
router.use(requireAdmin);

router.get('/wallet/stats', getHotWalletStats);
router.get('/liability', getPlatformLiability);

router.get('/users', getAllUsers);
router.put('/users/balance', updateUserBalance);

export default router;
