@echo off
title SIBOB - Git Pull & Update
echo ====================================================
echo      SIBOB - PULL UPDATE DARI GITHUB REPOSITORY
echo ====================================================
echo.

echo Menarik pembaruan dari branch main...
git pull origin main

if %errorlevel% neq 0 (
    echo.
    echo [PERHATIAN] Git pull mendeteksi bentrok file lokal.
    echo Menjalankan sinkronisasi penuh (git reset --hard)...
    git fetch origin
    git reset --hard origin/main
)

echo.
echo Menjalankan migrasi database (jika ada)...
php artisan migrate --force

echo.
echo Membersihkan cache view, route, dan config...
php artisan view:clear
php artisan route:clear
php artisan config:clear

echo.
echo ====================================================
echo  BERHASIL: Sistem telah sinkron dan siap digunakan!
echo ====================================================
echo.
pause
