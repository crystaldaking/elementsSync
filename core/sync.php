<?php
define('MODX_API_MODE', true);
require_once '../index.php';
$modx=new modX();
$modx->initialize('mgr');
$modx->setLogTarget('ECHO');
$modx->setLogLevel(MODX_LOG_LEVEL_INFO);

$savePath = '../elements/';

if ($argc < 1){
    echo 'U r missing paramets: import -> import to files; export -> import into MODX'.PHP_EOL;
    return 0;
}


/** @return bool
 * @var modX $modx
 */
function unstaticFunc($tableName,&$modx){
    $sql = "UPDATE {$tableName} SET static = 0";
    $q = $modx->prepare($sql);
    if (!$q->execute()){
        $modx->log(1,print_r($q->errorInfo(),true));
        return false;
    }
    return true;
}

function rglob($pattern, $flags = 0) {
    $files = glob($pattern, $flags);
    foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
        $files = array_merge($files, rglob($dir.'/'.basename($pattern), $flags));
    }
    return $files;
}

if ($argv[1] == 'import') {
    /** @var modTemplate[] $templates */
    $templates = $modx->getIterator(modTemplate::class);
    /** @var modChunk[] $chunks */
    $chunks = $modx->getIterator(modChunk::class);
    /** @var modSnippet[] $snippets */
    $snippets = $modx->getIterator(modSnippet::class);
    /** @var modPlugin[] $plugins */
    $plugins = $modx->getIterator(modPlugin::class);

    foreach ($templates as $template) {
        /** @var modCategory $category */
        $category = $template->getOne('Category');
        $source = $template->toArray();
        $source['category'] = $category ? $category->category : null;
        if (!empty($source['content'])) {
            $content = $source['content'];
            $name = trim($source['templatename']) . '.tpl';

            if ($source['category'] != null){
                if (!is_dir($savePath.'templates/'.$source['category'])){
                    mkdir($savePath.'templates/'.$source['category']);
                }

                if (file_put_contents($savePath . 'templates/' .$source['category'].'/'. $name, $content) === false) {
                    $modx->log(MODX_LOG_LEVEL_ERROR, "Error while saving template into file: \"{$name}\"");
                    continue;
                }
            } else{
                if (file_put_contents($savePath . 'templates/' . $name, $content) === false) {
                    $modx->log(MODX_LOG_LEVEL_ERROR, "Error while saving template into file: \"{$name}\"");
                    continue;
                }
            }


            $template->set('static', 1);
            $template->set('source', 1);
            $template->set('static_file', substr($savePath,3) . 'templates/' . $name);
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

            if ($source['category'] != null){
                if (!is_dir($savePath.'chunks/'.$source['category'])){
                    mkdir($savePath.'chunks/'.$source['category']);
                }

                if (file_put_contents($savePath . 'chunks/' .$source['category'].'/'. $name, $content) === false) {
                    $modx->log(MODX_LOG_LEVEL_ERROR, "Error while saving chunk into file: \"{$name}\"");
                    continue;
                }
                $chunk->set('static_file',substr($savePath,3).'chunks/' .$source['category'].'/'. $name);
            } else{
                if (file_put_contents($savePath . 'chunks/' . $name, $content) === false) {
                    $modx->log(MODX_LOG_LEVEL_ERROR, "Error while saving chunk into file: \"{$name}\"");
                    continue;
                }
                $chunk->set('static_file',substr($savePath,3).'chunks/'.$name);
            }

            $chunk->set('static',1);
            $chunk->set('source',1);
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

            if ($source['category'] != null){
                if (!is_dir($savePath.'snippets/'.$source['category'])){
                    mkdir($savePath.'snippets/'.$source['category']);
                }

                if (file_put_contents($savePath . 'snippets/' .$source['category'].'/'. $name, $content) === false) {
                    $modx->log(MODX_LOG_LEVEL_ERROR, "Error while saving snippet into file: \"{$name}\"");
                    continue;
                }
                $snippet->set('static_file',substr($savePath,3).'snippets/' .$source['category'].'/'. $name);
            }
            else {
                if (file_put_contents($savePath . 'snippets/' . $name, $content) === false) {
                    $modx->log(MODX_LOG_LEVEL_ERROR, "Error while saving snippet into file: \"{$name}\"");
                    continue;
                }
                $snippet->set('static_file',substr($savePath,3).'snippets/'.$name);
            }

            $snippet->set('static',1);
            $snippet->set('source',1);

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

            if ($source['category'] != null){
                if (!is_dir($savePath.'plugins/'.$source['category'])){
                    mkdir($savePath.'plugins/'.$source['category']);
                }

                if (file_put_contents($savePath . 'plugins/' .$source['category'].'/'. $name, $content) === false) {
                    $modx->log(MODX_LOG_LEVEL_ERROR, "Error while saving plugin into file: \"{$name}\"");
                    continue;
                }
                $plugin->set('static_file',substr($savePath,3).'plugins/' .$source['category'].'/'. $name);
            }
            else {
                if (file_put_contents($savePath . 'plugins/' . $name, $content) === false) {
                    $modx->log(MODX_LOG_LEVEL_ERROR, "Error while saving plugin into file: \"{$name}\"");
                    continue;
                }
                $plugin->set('static_file',substr($savePath,3).'plugins/'.$name);
            }

            $plugin->set('static',1);
            $plugin->set('source',1);

            if ($plugin->save()){
                $modx->log(MODX_LOG_LEVEL_INFO, "Plugin was imported: \"{$name}\"");
            }
        }
    }
}

if ($argv[1] == 'export'){
    $templateFiles = rglob(MODX_BASE_PATH.'elements/templates/*.tpl');
    $chunkFiles = rglob(MODX_BASE_PATH.'elements/chunks/*.tpl');
    $pluginFiles = rglob(MODX_BASE_PATH.'elements/plugins/*.php');
    $snippetFiles = rglob(MODX_BASE_PATH.'elements/snippets/*.php');

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
        $pluginEvent = file($pluginFile)[0];

        if (strlen($pluginEvent) > 0) {
            if ($pluginEvent[0] == '#'){
                $pluginEvent = str_replace("#","",$pluginEvent);
            }
        }
        unset(file($pluginFile)[0]);
        file_put_contents($pluginFile,implode('',file($pluginFile)));

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
                $event = $modx->newObject(modPluginEvent::class);
                $event->set('pluginid',$plugin->get('id'));
                $event->set('priority',0);
                $event->set('propertyset',0);
                $event->set('event',$pluginEvent);
                if (!$event->save()){
                    $modx->log(MODX_LOG_LEVEL_ERROR, 'Can not save event plugin ' . $name);
                }
                $modx->log(MODX_LOG_LEVEL_INFO,'Saved new plugin: '.$name);
            } else {
                $modx->log(MODX_LOG_LEVEL_ERROR, 'Can not save plugin ' . $name);
            }
        }
    }
}


if ($argv[1] == 'deploy'){
    $templates = $modx->getTableName(modTemplate::class);
    $chunks = $modx->getTableName(modChunk::class);
    $plugins = $modx->getTableName(modPlugin::class);
    $snippets = $modx->getTableName(modSnippet::class);

    unstaticFunc($templates,$modx);
    unstaticFunc($chunks,$modx);
    unstaticFunc($plugins,$modx);
    unstaticFunc($snippets,$modx);

    $modx->log(MODX_LOG_LEVEL_INFO,'Done');
}

$cache = $modx->getCacheManager();
$cache->refresh();
