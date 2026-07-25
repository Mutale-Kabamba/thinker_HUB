$code = @"
using System;
using System.IO;
using System.Text;

public class LogDecoder3 {
    public static string Decode(string path) {
        byte[] bytes = File.ReadAllBytes(path);
        for (int i = 0; i < bytes.Length; i++) {
            byte b = bytes[i];
            if ((b >= 32 && b <= 126) || b == 13 || b == 10 || b == 9) {
                // keep
            } else {
                bytes[i] = 32;
            }
        }
        return Encoding.ASCII.GetString(bytes);
    }
}
"@
Add-Type -TypeDefinition $code -ErrorAction SilentlyContinue

$logPath = "storage/logs/laravel.log"
$text = [LogDecoder3]::Decode($logPath)
$lines = $text -split "\r\n|\n|\r"
$collapsed = $lines | ForEach-Object { $_ -replace ' +', ' ' }
$filtered = $collapsed | Where-Object { $_ -match 'local.ERROR|SQLSTATE|course_instructor|bio|start_time|instructor|CourseResource' }
$last80 = $filtered | Select-Object -Last 80
$last80 | Out-String
