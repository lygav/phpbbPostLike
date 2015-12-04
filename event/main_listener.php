<?php

namespace lygav\phpbbPostLike\event;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class main_listener implements EventSubscriberInterface
{
    static public function getSubscribedEvents()
    {
        return array(
            'core.page_footer' => 'include_extension_common_js',
            'core.page_header' => 'include_extension_common_css',
            'core.viewtopic_modify_post_row' => 'add_like_button'
        );

    }

    /* @var \phpbb\controller\helper */
    protected $helper;
    /* @var \phpbb\template\template */
    protected $template;

    /**
     * Constructor
     *
     * @param \phpbb\controller\helper $helper Controller helper object
     * @param \phpbb\template $template Template object
     * @param \phpbb\db\driver $db Database object
     */
    public function __construct(
        \phpbb\controller\helper $helper,
        \phpbb\template\template $template,
        \phpbb\db\driver\driver_interface $db,
        \phpbb\user $user,
        $table_prefix
    )
    {
        $this->helper = $helper;
        $this->template = $template;
        $this->db = $db;
        $this->table_prefix = $table_prefix;
        $this->user = $user;
    }

    public function include_extension_common_js()
    {
    }

    public function include_extension_common_css()
    {
    }

    public function add_like_button($event)
    {
        $user_liked = FALSE;
        $others_liked = FALSE;
        $this->user->add_lang_ext('lygav/phpbbPostLike', ['common']);
        $post_row = $event['post_row'];
        if ($this->user->data['user_type'] == 1 OR $this->user->data['user_type'] == 2) {
            $post_row['SHOW_ANON_LIKES'] = TRUE;
            $sql = 'SELECT
				COUNT(user_id) as count
				FROM ' . $this->table_prefix . 'post_likes
				WHERE post_id = ' . $event['post_row']['POST_ID'];
            $this->db->sql_query($sql);
            if ($count = $this->db->sql_fetchfield("count")) {
                $post_row["OTHER_LIKES_COUNT"] = $count;
            }
        } else {
            $sql = 'SELECT
				user_id
				FROM ' . $this->table_prefix . 'post_likes
				WHERE post_id = ' . $event['post_row']['POST_ID'];
            $this->db->sql_query($sql);
            $likes_count = 0;
            while ($liker_id = $this->db->sql_fetchfield("user_id")) {
                if ($liker_id == $this->user->data['user_id']) {
                    $user_liked = TRUE;
                } else {
                    $others_liked = TRUE;
                    $likes_count += 1;
                    $post_row["OTHER_LIKES_COUNT"] = $likes_count;
                }
            }
            $like_opt = "opt2";
            if ($user_liked and $others_liked) {
                $post_row['EVERYBODY_LIKE'] = TRUE;
            } else if ($user_liked) {
                $post_row['ONLY_USER_LIKE'] = TRUE;
            } else if ($others_liked) {
                $post_row['ONLY_OTHERS_LIKE'] = TRUE;
                $like_opt = "opt1";
            }
            $post_row["LIKE_QUERY_DATA"] = http_build_query(["sid" => $this->user->session_id, 'like_opt' => $like_opt]);
        }
        $this->db->sql_freeresult();
        $event['post_row'] = $post_row;
    }
}