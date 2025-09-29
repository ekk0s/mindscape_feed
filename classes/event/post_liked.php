<?php
// This file is part of Moodle - http://moodle.org/
//
// GPL header...

namespace local_mindscape_feed\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Evento disparado quando um usuário curte um post no feed.
 *
 * objectid = ID do registro em local_mindscape_likes
 * other['postid'] = ID do post curtido
 */
class post_liked extends \core\event\base {

    /**
     * Nome do evento.
     */
    public static function get_name(): string {
        return get_string('eventpostliked', 'local_mindscape_feed');
    }

    /**
     * Descrição do evento.
     */
    public function get_description(): string {
        $postid = $this->other['postid'] ?? 0;
        return "O usuário com id '{$this->userid}' curtiu o post com id '{$postid}' no Mindscape Feed.";
    }

    /**
     * URL associada à ação (com âncora para o post).
     */
    public function get_url(): \moodle_url {
        $postid = $this->other['postid'] ?? null;
        // Terceiro parâmetro do moodle_url é a âncora (sem o '#').
        return new \moodle_url('/local/mindscape_feed/index.php', null, $postid ? ('p'.$postid) : null);
    }

    /**
     * Inicialização de metadados do evento.
     * IMPORTANTE: definir objecttable quando houver objectid.
     */
    protected function init(): void {
        $this->data['crud']      = 'c'; // create
        $this->data['edulevel']  = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'local_mindscape_likes';
    }
}
