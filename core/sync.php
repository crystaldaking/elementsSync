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
    $files = glob(MODX_BASE_PATH.'elements/templates/*.tpl');
    $firstTemplate = $modx->getObject(modTemplate::class,1);
    $indexSaveState = false;

    foreach ($files as $file){
        $content = file_get_contents($file);
        if (empty($content)) { continue; }
        $name = end(explode('/',$file));
        $name = trim(preg_replace('#\.tpl$#ui','',$name));
        $relative = str_replace(MODX_BASE_PATH,'',$file);

        /** @var modTemplate $template */
        if ($template = $modx->getObject(modTemplate::class,[
            'static_file' => $relative
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
}

$cache = $modx->getCacheManager();
$cache->refresh();
