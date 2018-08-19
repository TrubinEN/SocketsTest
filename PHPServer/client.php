#!/usr/bin/env php
<?php
// получим параметры из командной строки
$params = getopt('', ['address::', 'port::', "message"]);
// параметры соединения
$address = $params['address'] ?? '127.0.0.1';
$port = $params['port'] ?? 9999; // более 1024 менее 64000
$message = $params['message'] ?? "GET /";
$message .= "\n";


while(true)
{
	usleep(100000);
	// создаем сокет
	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	if ($socket === false)
	    die("Socket create failed: " . socket_strerror(socket_last_error())) . "\n";

	$connect = socket_connect($socket, $address, $port);
	if ($connect === false) 
	    die("Socket create failed: " . socket_strerror(socket_last_error())) . "\n";

	// отправим сообщение для сервера, в данном случае мы просим открыть главную страницу
	socket_write($socket, $message, strlen($message));

	// ожидаем ответ, второй параметр размер ожидаемого ответа
	$answer = '';
	while(($line = socket_read($socket, 2024)) !== ""){
	    $answer .= $line;
	}

	// выводим ответ
	echo $answer . "\n";

	// закрываем соединение
	socket_close($socket);
}
