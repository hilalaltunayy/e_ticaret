Get-ChildItem -Recurse -Include *.php,*.js,*.css,*.html,*.json |
ForEach-Object {
    $content = Get-Content $_.FullName -Raw
    if ($content -match 'Ã|â|ƒ|Æ|€|�') {
        Write-Output "Encoding problem found in: $($_.FullName)"
    }
}
