<?php

declare(strict_types=1);

final class HomeController
{
    public function index(): void
    {
        $databaseConnection = getDBConnection();
        $user = currentUser();

        renderView('home/index', [
            'pageTitle' => 'Camagru',
            'isDatabaseConnected' => $databaseConnection instanceof PDO,
            'currentUser' => $user,
        ]);
    }
}

