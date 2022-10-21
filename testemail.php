<?php

$headers="From: noreply@elegendz.net" . "\r\n" . "Reply-To: noreply@elegendz.net" . "\r\n" . "X-Mailer: PHP/" . phpversion();
mail('freelance.frivas@gmail.com', 'Elegendz cron', 'test',$headers);
