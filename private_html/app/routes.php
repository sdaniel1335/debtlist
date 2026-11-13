<?php
return array(
  'GET' => array(
    '/' => array('controller' => 'pages', 'class' => 'pages', 'action' => 'index'),
    '/item' => array('controller' => 'pages', 'class' => 'pages', 'action' => 'edit'),
    '/rates' => array('controller' => 'pages', 'class' => 'pages', 'action' => 'rates'),
    '/login' => array('controller' => 'auth', 'class' => 'auth', 'action' => 'login')
  ),
  'POST' => array(
    '/item' => array('controller' => 'pages', 'class' => 'pages', 'action' => 'save'),
    '/delete' => array('controller' => 'pages', 'class' => 'pages', 'action' => 'delete'),
    '/rates' => array('controller' => 'pages', 'class' => 'pages', 'action' => 'save_rates'),
    '/login' => array('controller' => 'auth', 'class' => 'auth', 'action' => 'login_post'),
    '/logout' => array('controller' => 'auth', 'class' => 'auth', 'action' => 'logout')
  )
);
