<?php
// Replicate the exact pattern from functions.php
$upload_url = 'http://local-skylark-20-09-26.test/wp-content/uploads';
$escaped_url = preg_quote($upload_url, '#');
$pattern = '#(' . $escaped_url . '/[^\s"\')\,\>\;]+\.(jpe?g|png|gif|bmp|tiff?))(?=[\s"\')\,\>\;])#i';

echo "Pattern: $pattern\n\n";

$test1 = 'url(http://local-skylark-20-09-26.test/wp-content/uploads/2026/05/new-logo-skylark-modified.png);"';
$test2 = 'src="http://local-skylark-20-09-26.test/wp-content/uploads/2026/05/new-logo-skylark-modified.png">';

$r1 = preg_match($pattern, $test1, $m1);
echo "Test1 [$test1]: " . ($r1 ? "MATCH => {$m1[1]}" : "NO MATCH") . "\n";

$r2 = preg_match($pattern, $test2, $m2);
echo "Test2 [$test2]: " . ($r2 ? "MATCH => {$m2[1]}" : "NO MATCH") . "\n";

// Now fetch actual HTML and test
$ctx = stream_context_create(['http' => ['header' => "Accept: text/html,image/webp\r\n"]]);
$html = file_get_contents('http://local-skylark-20-09-26.test/', false, $ctx);

// Find the LoftLoader div section
$loft_start = strpos($html, 'loftloader-wrapper');
if ($loft_start !== false) {
    $section = substr($html, $loft_start, 800);
    echo "\n--- LoftLoader section (raw) ---\n$section\n---\n\n";
    
    $r3 = preg_match_all($pattern, $section, $m3);
    echo "Matches in LoftLoader section: $r3\n";
    if ($r3) foreach ($m3[1] as $u) echo "  => $u\n";
}
