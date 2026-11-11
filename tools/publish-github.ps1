$ErrorActionPreference = 'Stop'
$projectPath = Split-Path -Parent $PSScriptRoot
Set-Location $projectPath
$targetRepository = 'https://github.com/feryHendrix-ui/JSONDecode.git'

if (-not (Get-Command git -ErrorAction SilentlyContinue)) {
    throw 'Git belum tersedia pada terminal ini.'
}
if (-not (Test-Path '.git')) {
    git init -b main
    if ($LASTEXITCODE -ne 0) { throw 'Git init gagal.' }
}
$remoteNames = @(git remote)
if ($LASTEXITCODE -ne 0) { throw 'Tidak dapat membaca daftar remote Git.' }
if ($remoteNames -notcontains 'origin') {
    git remote add origin $targetRepository
    if ($LASTEXITCODE -ne 0) { throw 'Gagal menambahkan origin.' }
} else {
    $originUrl = git remote get-url origin
    if ($LASTEXITCODE -ne 0) { throw 'Tidak dapat membaca origin.' }
    if ($originUrl -notin @($targetRepository, 'https://github.com/feryHendrix-ui/JSONDecode', 'git@github.com:feryHendrix-ui/JSONDecode.git')) {
        throw 'Origin mengarah ke repository lain. Periksa git remote -v terlebih dahulu.'
    }
}
git add .
if ($LASTEXITCODE -ne 0) { throw 'Git add gagal.' }
git diff --cached --quiet
if ($LASTEXITCODE -eq 1) {
    git commit -m 'Add Laravel 12 public API teaching project'
    if ($LASTEXITCODE -ne 0) { throw 'Commit gagal. Periksa identitas Git Anda.' }
}
git push -u origin HEAD:main
if ($LASTEXITCODE -ne 0) {
    throw 'Push belum berhasil. Periksa login dan izin tulis repository. Script tidak memakai force push.'
}
Write-Host 'Kode berhasil diunggah ke repository tujuan.'
