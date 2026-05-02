const express = require('express');
const { login, getMe } = require('./auth.controller');
const { protect } = require('../../middlewares/auth.middleware');

const router = express.Router();

router.post('/login', login);
router.get('/me', protect, getMe);

module.exports = router;