"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const express_1 = require("express");
const auth_1 = require("../middleware/auth");
const admin_controller_1 = require("../controllers/admin.controller");
const adminUsers_controller_1 = require("../controllers/adminUsers.controller");
const router = (0, express_1.Router)();
// Protect all admin routes with requireAdmin middleware
router.use(auth_1.requireAdmin);
router.get('/wallet/stats', admin_controller_1.getHotWalletStats);
router.get('/liability', admin_controller_1.getPlatformLiability);
router.get('/users', adminUsers_controller_1.getAllUsers);
router.put('/users/balance', adminUsers_controller_1.updateUserBalance);
exports.default = router;
