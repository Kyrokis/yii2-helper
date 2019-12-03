<?php

use yii\db\Migration;

/**
 * Class m191203_092822_add_column_del
 */
class m191203_092822_add_column_del extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{	
		$this->execute("CREATE TYPE del AS ENUM ('0','1')");
		$this->addColumn('items', 'del', "del NOT NULL DEFAULT '0'");	
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->execute('DROP TYPE del');
		$this->dropColumn('items', 'del');
	}
}
