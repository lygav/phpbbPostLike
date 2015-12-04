<?php

namespace lygav\phpbbPostLike\migrations;

class r1 extends \phpbb\db\migration\migration
{
	public function update_data ()
	{
		return [
			['custom', [[$this, 'add_timestamp_to_table']]]
		];
	}

	public function update_schema ()
	{
		return [
			'add_tables' => [
				$this->table_prefix . 'post_likes' => [
					'COLUMNS'     => [
						'post_id'   => ['UINT:8', 0],
						'user_id'   => ['UINT:8', 0],
					],
					'PRIMARY_KEY' => [
						'user_id',
						'post_id'
					],
					'KEYS' => [
							'u_i_d' => ['INDEX', 'user_id']
						]
				]
			]
		];
	}

	public function add_timestamp_to_table ()
	{
		$this->db->sql_query("ALTER TABLE  `phpbb_post_likes` ADD  `occured_on` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER  `user_id`");
	}

	public function revert_schema ()
	{
		return [
			'drop_tables' => [
				$this->table_prefix.'post_likes'
			]
		];
	}


}