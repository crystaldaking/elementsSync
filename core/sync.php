<?php
define('MODX_API_MODE', true);
require_once '../index.php';
$modx=new modX();
$modx->initialize('mgr');
$modx->setLogTarget('ECHO');
$modx->setLogLevel(MODX_LOG_LEVEL_INFO);

$savePath = MODX_BASE_PATH.'elements/';

if ($argc < 1){
    echo 'U r missing paramets: import -> import to files; export -> import into MODX'.PHP_EOL;
    return 0;
}

if ($argv[1] == 'import') {
    /** @var modTemplate[] $templates */
    $templates = $modx->getCollection(modTemplate::class);
    /** @var modChunk[] $chunks */
    $chunks = $modx->getCollection(modChunk::class);
    /** @var modSnippet[] $snippets */
    $snippets = $modx->getCollection(modSnippet::class);
    /** @var modPlugin[] $plugins */
    $plugins = $modx->getCollection(modPlugin::class);

    foreach ($templates as $template) {
        /** @var modCategory $category */
        $category = $template->getOne('Category');
        $source = $template->toArray();
        $source['category'] = $category ? $category->category : null;
        if (!empty($source['content'])) {
            $content = $source['content'];
            $name = trim($source['templatename']) . '.tpl';
            if (file_put_contents($savePath . 'templates/' . $name, $content) === false) {
                $modx->log(MODX_LOG_LEVEL_ERROR, "Error while saving template into file: \"{$name}\"");
                continue;
            }
            $template->set('static', 1);
            $template->set('source', 1);
            $template->set('static_file', $savePath . 'templates/' . $name);
            if ($template->save()) {
                $modx->log(MODX_LOG_LEVEL_INFO, "Template was imported: \"{$name}\"");
            }
        }
    }
    foreach ($chunks as $chunk){
        /** @var modCategory $category */
        $category = $chunk->getOne('Category');
        $source = $chunk->toArray();
        $source['category'] = $category ? $category->category : null;
        if (!empty($source['content'])){
            $content = $source['content'];
            $name = trim($source['name']) . '.tpl';
            if (file_put_contents($savePath . 'chunks/' . $name, $content) === false) {
                $modx->log(MODX_LOG_LEVEL_ERROR, "Error while saving chunk into file: \"{$name}\"");
                continue;
            }
            $chunk->set('static',1);
            $chunk->set('source',1);
            $chunk->set('static_file',$savePath.'chunks/'.$name);
            if ($chunk->save()){
                $modx->log(MODX_LOG_LEVEL_INFO, "Chunk was imported: \"{$name}\"");
            }
        }
    }
    foreach ($snippets as $snippet){
        /** @var modCategory $category */
        $category = $snippet->getOne('Category');
        $source = $snippet->toArray();
        $source['category'] = $category ? $category->category : null;
        if (!empty($source['content'])){
            $content = $source['content'];
            $name = trim($source['name']) . '.php';
            if (file_put_contents($savePath . 'snippets/' . $name, $content) === false) {
                $modx->log(MODX_LOG_LEVEL_ERROR, "Error while saving snippet into file: \"{$name}\"");
                continue;
            }
            $snippet->set('static',1);
            $snippet->set('source',1);
            $snippet->set('static_file',$savePath.'snippets/'.$name);
            if ($snippet->save()){
                $modx->log(MODX_LOG_LEVEL_INFO, "Snippet was imported: \"{$name}\"");
            }
        }
    }
    foreach ($plugins as $plugin) {
        /** @var modCategory $category */
        $category = $plugin->getOne('Category');
        $source = $plugin->toArray();
        $source['category'] = $category ? $category->category : null;
        if (!empty($source['content'])){
            $content = $source['plugincode'];
            $name = trim($source['name']) . '.php';
            if (file_put_contents($savePath . 'plugins/' . $name, $content) === false) {
                $modx->log(MODX_LOG_LEVEL_ERROR, "Error while saving plugin into file: \"{$name}\"");
                continue;
            }
            $plugin->set('static',1);
            $plugin->set('source',1);
            $plugin->set('static_file',$savePath.'plugins/'.$name);
            if ($plugin->save()){
                $modx->log(MODX_LOG_LEVEL_INFO, "Plugin was imported: \"{$name}\"");
            }
        }
    }
}

if ($argv[1] == 'export'){
    $templateFiles = glob(MODX_BASE_PATH.'elements/templates/*.tpl');
    $chunkFiles = glob(MODX_BASE_PATH.'elements/chunks/*.tpl');
    $pluginFiles = glob(MODX_BASE_PATH.'elements/plugins/*.php');
    $snippetFiles = glob(MODX_BASE_PATH.'elements/snippets/*.php');

    $firstTemplate = $modx->getObject(modTemplate::class,1);
    $indexSaveState = false;

    foreach ($templateFiles as $templateFile){
        $content = file_get_contents($templateFile);
        if (empty($content)) { continue; }
        $name = end(explode('/',$templateFile));
        $name = trim(preg_replace('#\.tpl$#ui','',$name));
        $relative = str_replace(MODX_BASE_PATH,'',$templateFile);

        /** @var modTemplate $template */
        if ($template = $modx->getObject(modTemplate::class,[
            'templatename' => $name
        ])){
            $modx->log(MODX_LOG_LEVEL_INFO,'Template '.$name.' is already in the database');
            continue;
        }


        if ($name == 'index' || $name == 'home' || $name == 'BaseTemplate'){
            $template = &$firstTemplate;
            $indexSaveState = true;
        } else {
            $template = $modx->newObject(modTemplate::class);
            $template->set('source',1);
            $template->set('templatename',$name);
            $template->set('static',true);
            $template->set('static_file',$relative);
            if ($template->save()){
                $modx->log(MODX_LOG_LEVEL_INFO,'Saved new template: '.$name);
            } else {
                $modx->log(MODX_LOG_LEVEL_ERROR,'Can not save template '.$name);
            }
        }

    }
    foreach ($chunkFiles as $chunkFile){
        $content = file_get_contents($chunkFile);
        if (empty($content)) {continue;}
        $name = end(explode('/',$chunkFile));
        $name = trim(preg_replace('#\.tpl$#ui','',$name));
        $relative = str_replace(MODX_BASE_PATH,'',$chunkFile);

        if ($chunk = $modx->getObject(modChunk::class,[
            'name' => $name
        ])){
            $modx->log(MODX_LOG_LEVEL_INFO,'Chunk '.$name.' is already in the database');
            continue;
        } else {
            $chunk = $modx->newObject(modChunk::class);
            $chunk->set('source',1);
            $chunk->set('name',$name);
            $chunk->set('static',true);
            $chunk->set('static_file',$relative);
            if ($chunk->save()){
                $modx->log(MODX_LOG_LEVEL_INFO,'Saved new chunk: '.$name);
            } else {
                $modx->log(MODX_LOG_LEVEL_ERROR, 'Can not save chunk ' . $name);
            }
        }


    }
    foreach ($snippetFiles as $snippetFile){
        $content = file_get_contents($snippetFile);
        if (empty($content)) {continue;}
        $name = end(explode('/',$snippetFile));
        $name = trim(preg_replace('#\.php$#ui','',$name));
        $relative = str_replace(MODX_BASE_PATH,'',$snippetFile);

        if ($snippet = $modx->getObject(modSnippet::class,[
            'name' => $name
        ])){
            $modx->log(MODX_LOG_LEVEL_INFO,'Snippet '.$name.' is already in the database');
            continue;
        } else {
            $snippet = $modx->newObject(modSnippet::class);
            $snippet->set('source',1);
            $snippet->set('name',$name);
            $snippet->set('static',true);
            $snippet->set('static_file',$relative);
            if ($snippet->save()){
                $modx->log(MODX_LOG_LEVEL_INFO,'Saved new snippet: '.$name);
            } else {
                $modx->log(MODX_LOG_LEVEL_ERROR, 'Can not save snippet ' . $name);
            }
        }


    }
    foreach ($pluginFiles as $pluginFile){
        $content = file_get_contents($pluginFile);
        if (empty($content)) {continue;}
        $name = end(explode('/',$pluginFile));
        $name = trim(preg_replace('#\.php$#ui','',$name));
        $relative = str_replace(MODX_BASE_PATH,'',$pluginFile);

        if ($plugin = $modx->getObject(modPlugin::class,[
            'name' => $name
        ])){
            $modx->log(MODX_LOG_LEVEL_INFO,'Plugin '.$name.' is already in the database');
            continue;
        } else {
            $plugin = $modx->newObject(modPlugin::class);
            $plugin->set('source',1);
            $plugin->set('name',$name);
            $plugin->set('static',true);
            $plugin->set('static_file',$relative);
            if ($plugin->save()){
                $modx->log(MODX_LOG_LEVEL_INFO,'Saved new plugin: '.$name);
            } else {
                $modx->log(MODX_LOG_LEVEL_ERROR, 'Can not save plugin ' . $name);
            }
        }

    }

}


if ($argv[1] == 'deploy'){
    $templates = $modx->getCollection(modTemplate::class);
    $chunks = $modx->getCollection(modChunk::class);
    $plugins = $modx->getCollection(modPlugin::class);
    $snippets = $modx->getCollection(modSnippet::class);

    foreach ($templates as $template){
        if (is_object($template)){
            $template->set('static',0);
            $template->save();
        }
    }

    foreach ($chunks as $chunk){
        if (is_object($chunk)){
            $chunk->set('static',0);
            $chunk->save();
        }
    }

    foreach ($plugins as $plugin){
        if (is_object($plugin)){
            $plugin->set('static',0);
            $plugin->save();
        }
    }

    foreach ($snippets as $snippet){
        if (is_object($snippet)){
            $snippet->set('static',0);
            $snippet->save();
        }
    }

    $modx->log(MODX_LOG_LEVEL_INFO,'Done');
}

$cache = $modx->getCacheManager();
$cache->refresh();
