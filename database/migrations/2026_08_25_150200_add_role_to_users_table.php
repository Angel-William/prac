<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| ROLE-BASED ACCESS CONTROL - the column everything else hangs off
|--------------------------------------------------------------------------
| [EXAM] PSRS Part 1.c - "Users can have roles such as admin, editor and viewer"
|
| [REUSE] We do NOT create a users table - the starter kit already made one in
|         0001_01_01_000000_create_users_table.php. We only ADD to it.
|         That is what `Schema::table` (not `Schema::create`) means.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // [LEARN] Use string(), NOT enum().
            //         enum() is unsupported by SQLite (your DB_CONNECTION is
            //         sqlite) and needs an extra package to ALTER on MySQL.
            //         A string plus validation does the same job and never
            //         fights you at 3 minutes to go.
            $table->string('role')->default('viewer')->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
