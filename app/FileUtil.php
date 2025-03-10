        closedir($dir);
    }
    
    /**
     * 创建示例索引文件
     * @param string $destDir 目标目录
     */
    protected static function createExamplesIndex($destDir)
    {
        $files = scandir($destDir);
        $examples = [];
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || $file === 'index.html') {
                continue;
            }
            
            if (pathinfo($file, PATHINFO_EXTENSION) === 'html') {
                $examples[] = [
                    'file' => $file,
                    'name' => self::formatExampleName($file),
                ];
            }
        }
        
        $html = <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OOS 示例列表</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        h1 {
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .example-list {
            list-style: none;
            padding: 0;
        }
        .example-list li {
            margin-bottom: 10px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #f9f9f9;
        }
        .example-list a {
            color: #4CAF50;
            font-weight: bold;
            text-decoration: none;
            font-size: 18px;
        }
        .example-list a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>OOS 示例列表</h1>
    
    <ul class="example-list">
HTML;
        
        foreach ($examples as $example) {
            $html .= <<<HTML
        <li>
            <a href="{$example['file']}">{$example['name']}</a>
        </li>
HTML;
        }
        
        $html .= <<<HTML
    </ul>
</body>
</html>
HTML;
        
        file_put_contents($destDir . '/index.html', $html);
    }
    
    /**
     * 格式化示例名称
     * @param string $filename 文件名
     * @return string 格式化后的名称
     */
    protected static function formatExampleName($filename)
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $name = str_replace('-', ' ', $name);
        $name = ucwords($name);
        return $name;
    }
}