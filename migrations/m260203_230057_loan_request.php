<?php

use yii\db\Migration;

class m260203_230057_loan_request extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%loan_request}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'amount'  => $this->integer()->notNull(),
            'term'    => $this->integer()->notNull(),
            'status' => $this->string()->notNull()->defaultValue('new'), // new | in_process | approved | declined
            'processed_at' => $this->timestamp()->null()->comment('Когда обработали заявку'),
            'created_at'   => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at'   => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->execute("
            CREATE INDEX idx_loan_request_user_status ON loan_request (user_id, status)
        ");

        $this->execute("
            CREATE UNIQUE INDEX ux_loan_request_user_approved
            ON loan_request (user_id)
            WHERE status = 'approved'
        ");
    }

    public function safeDown()
    {
        $this->dropTable('{{%loan_request}}');
    }
}
