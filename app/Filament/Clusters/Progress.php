<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class Progress extends Cluster
{
    protected static ?int $navigationSort = 10;
    protected static ?string $navigationIcon = 'heroicon-s-chart-bar';
}
