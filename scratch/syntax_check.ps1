$files = Get-ChildItem -Path app,routes,bootstrap,database,tests -Filter *.php -Recurse
$errors = 0
foreach ($file in $files) {
    $res = php -l $file.FullName 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-Host "SYNTAX ERROR in $($file.FullName):" -ForegroundColor Red
        Write-Host $res
        $errors++
    }
}
if ($errors -eq 0) {
    Write-Host "All $($files.Count) PHP files passed syntax check!" -ForegroundColor Green
} else {
    Write-Host "$errors file(s) failed syntax check." -ForegroundColor Red
}
