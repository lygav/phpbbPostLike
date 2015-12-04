<?php

namespace lygav\phpbbPostLike\controller;

class controller
{
	/** @var \phpbb\config\config */
	protected $config;
	/** @var \phpbb\config\db_text */
	protected $config_text;
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;
	/** @var \phpbb\controller\helper */
	protected $helper;
	/** @var \phpbb\request\request */
	protected $request;
	/** @var \phpbb\user */
	protected $user;
	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config                $config         Config object
	 * @param \phpbb\config\db_text               $config_text    DB text object
	 * @param \phpbb\db\driver\driver_interface   $db             Database object
	 * @param \phpbb\controller\helper            $helper         Controller helper object
	 * @param \phpbb\request\request              $request        Request object
	 * @param \phpbb\user                         $user           User object
	 * @return \lygav\phpbbPostLike\controller\controller
	 * @access public
	 */
	public function __construct(
		\phpbb\config\config $config,
	    \phpbb\config\db_text $config_text,
	    \phpbb\db\driver\driver_interface $db,
	    \phpbb\controller\helper $helper,
	    \phpbb\request\request $request,
	    \phpbb\user $user,
		\phpbb\template\template $template,
		$table_prefix
	) {
		$this->config = $config;
		$this->config_text = $config_text;
		$this->db = $db;
		$this->helper = $helper;
		$this->request = $request;
		$this->user = $user;
		$this->template = $template;
		$this->table_prefix = $table_prefix;
		$this->set_custom_error_handler();
	}

	private function set_custom_error_handler ()
	{
		set_error_handler(function($errno, $errstr, $errfile, $errline) {
			return $errno == 256;
		});
	}

	public function like ($post_id)
	{

		if ($this->user->data['user_type'] != 1 AND $this->user->data['user_type'] != 2) {
			$json_response = new \phpbb\json_response();
			$user_id       = $this->user->data['user_id'];
			if ($post_id AND $user_id) {
				$this->db->sql_query(
					"INSERT INTO " . $this->table_prefix . "post_likes (`post_id`, `user_id`)
					VALUES ('" . $post_id . "', '" . $user_id . "')");
				$err = $this->db->get_sql_error_returned();
				if ($err['code'] == 1062) {
					$json_response->send(['status' => 'already liked']);
				}
			}
			$json_response->send([
				'status' => 'ok',
				'postId' => $post_id,
				'message' => $this->getLikeMessage($this->request->variable('like_opt', 'opt2'))
			]);
		}
	}

	private function getLikeMessage($like_opt_num)
	{
		$this->user->add_lang_ext('lygav/phpbbPostLike', ['common']);
		return $like_opt_num == "opt2" ? $this->user->lang['YOU_LIKE_THIS'] : $this->user->lang['YOU_AND'];
	}

} 