<?php

class WpProQuiz_Model_ToplistMapper extends WpProQuiz_Model_Mapper
{

    public function countFree($quizId, $name, $email, $ip, $clearTime = null)
    {
        $c = '';
        $args = array($quizId, $name, $email, $ip);

        if ($clearTime !== null) {
            $c = 'AND date >= %d';
            $args[] = time() - $clearTime;
        }

        $flooding = time() - 15;

        return $this->_wpdb->get_var(
            $this->_wpdb->prepare(
                "SELECT COUNT(*)
					FROM {$this->_tableToplist} 
					WHERE quiz_id = %d AND (name = %s OR email = %s OR (ip = %s AND date >= {$flooding})) " . $c,
                $args
            )
        );
    }

    public function countUser($quizId, $userId, $clearTime = null)
    {
        $c = '';
        $args = array($quizId, $userId);

        if ($clearTime !== null) {
            $c = 'AND date >= %d';
            $args[] = time() - $clearTime;
        }

        return $this->_wpdb->get_var(
            $this->_wpdb->prepare(
                "SELECT	COUNT(*)
							FROM {$this->_tableToplist}
							WHERE quiz_id = %d AND user_id = %d " . $c,
                $args
            )
        );
    }

    public function count($quizId)
    {
        return $this->_wpdb->get_var(
            $this->_wpdb->prepare(
                "SELECT	COUNT(*) FROM {$this->_tableToplist} WHERE quiz_id = %d",
                $quizId
            )
        );
    }

    public function save(WpProQuiz_Model_Toplist $toplist)
    {
        $this->_wpdb->insert($this->_tableToplist,
            array(
                'quiz_id' => $toplist->getQuizId(),
                'user_id' => $toplist->getUserId(),
                'date' => $toplist->getDate(),
                'name' => $toplist->getName(),
                'email' => $toplist->getEmail(),
                'points' => $toplist->getPoints(),
                'result' => $toplist->getResult(),
                'ip' => $toplist->getIp()
            ),
            array('%d', '%d', '%d', '%s', '%s', '%d', '%f', '%s'));

        $toplist->setToplistId($this->_wpdb->insert_id);
    }

    /**
     * @param $quizId
     * @param $limit
     * @param $sort
     * @param int $start
     * @return WpProQuiz_Model_Toplist[]
     */
    public function fetch($quizId, $limit, $sort, $start = 0)
    {
        $r = array();

        $start = (int)$start;

        switch ($sort) {
            case WpProQuiz_Model_Quiz::QUIZ_TOPLIST_SORT_BEST:
                $s = 'ORDER BY result DESC';
                break;
            case WpProQuiz_Model_Quiz::QUIZ_TOPLIST_SORT_NEW:
                $s = 'ORDER BY date DESC';
                break;
            case WpProQuiz_Model_Quiz::QUIZ_TOPLIST_SORT_OLD:
            default:
                $s = 'ORDER BY date ASC';
                break;
        }

        $results = $this->_wpdb->get_results(
            $this->_wpdb->prepare(
                'SELECT
								*
							FROM
								' . $this->_tableToplist . '
							WHERE
								quiz_id = %d
							' . $s . '
							LIMIT %d, %d'
                , $quizId, $start, $limit),
            ARRAY_A);

        foreach ($results as $row) {
            $r[] = new WpProQuiz_Model_Toplist($row);
        }

        return $r;
    }

    public function delete($quizId, $toplistIds = null)
    {
        $quizId = (int)$quizId;

        if ($toplistIds === null) {
            return $this->_wpdb->delete($this->_tableToplist, array('quiz_id' => $quizId), array('%d'));
        }

        $ids = array_map('intval', (array)$toplistIds);
        $placeholders = implode(', ', array_fill(0, count($ids), '%d'));

        return $this->_wpdb->query(
            $this->_wpdb->prepare(
                "DELETE FROM {$this->_tableToplist} WHERE quiz_id = %d AND toplist_id IN($placeholders)",
                array_merge(array($quizId), $ids)
            )
        );
    }

}