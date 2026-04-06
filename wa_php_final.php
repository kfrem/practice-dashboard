<?php
$number = "447939823988";
$message = urlencode("Hello, I found The Practice website and would like to find out more about your services.");
header("Location: https://wa.me/{$number}?text={$message}");
exit;
