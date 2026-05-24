"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const express_1 = require("express");
const llc_controller_1 = require("../controllers/llc.controller");
const router = (0, express_1.Router)();
// Public Routes
router.post('/register-llc', llc_controller_1.registerLlc);
router.post('/contact', llc_controller_1.contactUs);
// Admin Routes
router.post('/admin/dispatch-wallet', llc_controller_1.dispatchWallet);
exports.default = router;
