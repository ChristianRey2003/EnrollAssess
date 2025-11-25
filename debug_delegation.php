<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\RoleDelegation;
use Illuminate\Support\Facades\Auth;

$output = "";
$instructors = User::where('role', 'instructor')->get();

foreach ($instructors as $user) {
    $output .= "User: {$user->full_name} (ID: {$user->user_id})\n";
    
    $delegations = RoleDelegation::where('delegatee_id', $user->user_id)->get();
    $output .= "  Raw Delegations Found: " . $delegations->count() . "\n";
    
    foreach ($delegations as $d) {
        $output .= "    - ID: {$d->id}, Permission: {$d->permission}, Status: {$d->status}\n";
        $output .= "      Starts: {$d->starts_at}, Expires: {$d->expires_at}\n";
        $output .= "      Now: " . now() . "\n";
        
        $isActive = $d->status === 'active' && 
                    ($d->starts_at === null || $d->starts_at <= now()) && 
                    ($d->expires_at === null || $d->expires_at > now());
        $output .= "      Is Active (Manual Check): " . ($isActive ? 'YES' : 'NO') . "\n";
    }

    $hasPermission = $user->hasPermission('assign_applicants');
    $output .= "  User::hasPermission('assign_applicants'): " . ($hasPermission ? 'YES' : 'NO') . "\n";
    $output .= "---------------------------------------------------\n";
}

file_put_contents('debug_output.txt', $output);
echo "Debug output written to debug_output.txt";
