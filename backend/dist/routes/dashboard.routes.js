"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const express_1 = require("express");
const dashboard_controller_1 = require("../controllers/dashboard.controller");
const auth_1 = require("../middleware/auth");
const router = (0, express_1.Router)();
// Apply middleware to all dashboard routes
router.use(auth_1.authenticateToken);
router.get('/user', dashboard_controller_1.getUserProfile);
router.get('/llc-stats', dashboard_controller_1.getLlcStats);
router.get('/llc-list', dashboard_controller_1.getLlcList);
exports.default = router;
