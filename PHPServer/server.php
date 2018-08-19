#!/usr/bin/env php
<?php
// получим параметры из командной строки 
$params = getopt('', ['address::', 'port::', 'threads::']); 
// параметры соединения 
$address = $params['address'] ?? '127.0.0.1'; // адрес на машину которая подключается, у нас все локально 
$port = $params['port'] ?? 9999; // более 1024 менее 64000 
$threads = $params['threads'] ?? 1;
// создаем сокет для подключения 
$acceptor = socket_create(AF_INET, SOCK_STREAM, SOL_TCP); 
if ($acceptor === false) 
    die("Socket create failed: " . socket_strerror(socket_last_error())) . "\n";

// указываем в опциях что можно перереиспользовать адрес
socket_set_option($acceptor, SOL_SOCKET, SO_REUSEADDR, 1);

// Привязывает имя, указанное в параметре address, к сокету, описанному в параметре socket
if(!socket_bind($acceptor, $address, $port))
    die("Socket create failed: " . socket_strerror(socket_last_error())) . "\n";

// Прослушивает входящие соединения на сокете
if(!socket_listen($acceptor, 1)) // максимальная очередь 1
    die("Socket create failed: " . socket_strerror(socket_last_error())) . "\n";

// для примера создадим 3 потока запросов(наш сервер сможет обслужить 3 запроса паралельно)
for($i = 0; $i < $threads; $i++)
{
// обработываем в несколько потоков
$pid = pcntl_fork();
if($pid === 0)
{
// обрабатываем запросы и отвечаем в цикле, для того что бы обрабатывать не один запрос
while(true)
{
    $socket = socket_accept($acceptor);
    echo "Accept connection $socket\n";

    // получим pid процесса
    $pid = posix_getpid();
    // отправим сообщение
    $message = "Hello from " . $pid . " proccess\n";
    socket_write($socket, $message);

    $command = socket_read($socket, 2024);
    $command = trim($command);
    echo "Retrieve command: $command\n";
    $message = "[" . $command . "]\n";
    socket_write($socket, $message);
    socket_close($socket); 
}
}
}

// ждем закрытие всех соединений
while(($cid = pcntl_waitpid(0, $status)) != -1)
{
    $exitcode = pcntl_wexitstatus($status);
    echo "Child $cid exited with status $exitcode";
}
// закрываем соединение
socket_close($acceptor);
