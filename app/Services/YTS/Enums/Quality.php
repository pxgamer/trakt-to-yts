<?php

declare(strict_types=1);

namespace App\Services\YTS\Enums;

enum Quality: string
{
    case Q_2160P = '2160p';
    case Q_1080P = '1080p';
    case Q_720P = '720p';
    case Q_3D = '3D';
}
