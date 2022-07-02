<?php

class SchemaToSqlController extends AppController
{

	public $uses = [];

	public $components = ['BcAuth', 'Cookie', 'BcAuthConfigure'];

	public function admin_index()
	{
		if($this->request->is('post')) {
			if(empty($this->request->data['CuSchema']['plugin']) ||
				empty($this->request->data['CuSchema']['version'])) {
					$this->BcMessage->setError('値を入力してください。');
			} else {
				$sql = $this->buildSql(
					$this->request->data['CuSchema']['plugin'],
					$this->request->data['CuSchema']['version']
				);
				if($sql) {
					$this->set('sql', $sql);
				} else {
					$this->BcMessage->setInfo('アップデートSQLはありません');
				}
			}
		}
		$this->set('plugins', $this->getPluginList());
	}

	public function getPluginList()
	{
		$plugins = ['Core' => 'Core'];
		foreach(App::path('Plugin') as $path) {
			$folder = new Folder($path);
			$files = $folder->read();
			if(empty($files[0])) continue;
			$plugins = array_merge($plugins, $files[0]);
		}
		return array_combine($plugins, $plugins);
	}

	public function buildSql($plugin, $version)
	{
		if($plugin === 'Core') {
			$pluginPath = BASER;
		} else {
			CakePlugin::load($plugin);
			$pluginPath = CakePlugin::path($plugin);
		}
		$path = $pluginPath . 'Config' . DS . 'update' . DS . $version;
		$folder = new Folder($path);
		$files = $folder->read();
		if(!$files) return [];
		$db = ConnectionManager::getDataSource('default');
		$oldPath = preg_replace('/\/$/', '', TMP);
		$oldFile = 'current.php';
		$db->writeCurrentSchema($oldPath . DS . $oldFile);
		$sql = [];
		foreach($files[1] as $file) {
			if (preg_match('/^(create|alter)_(.+)\.php$/', $file, $matches)) {
				$type = $matches[1];
				$class = Inflector::camelize($matches[2]);
			} else {
				continue;
			}
			switch($type) {
				case 'create':
					$sql[] = $this->createSql($class, $path, $file);
					break;
				case 'alter':
					$sql[] = $this->alterSql($class, $oldPath, $oldFile, $path, $file);
					break;
			}
		}
		return $sql;
	}

	public function createSql($class, $path, $file)
	{
		$db = ConnectionManager::getDataSource('default');
		$CakeSchema = ClassRegistry::init('CakeSchema');
		$schema = $CakeSchema->load(['name' => $class, 'path' => $path, 'file' => $file]);
		return $db->createSchema($schema);
	}

	public function alterSql($class, $oldPath, $oldFile, $newPath, $newFile)
	{
		$db = ConnectionManager::getDataSource('default');
		$CakeSchema = ClassRegistry::init('CakeSchema');
		$oldClass = Inflector::camelize(preg_replace('/\.php$/', '', $oldFile));
		$old = $CakeSchema->load(['name' => $oldClass, 'path' => $oldPath, 'file' => $oldFile]);
		$new = $CakeSchema->load(['name' => $class, 'path' => $newPath, 'file' => $newFile]);
		$compare = $CakeSchema->compare($old, $new);
		return $db->alterSchema($compare);
	}
}
