<?php
// Strings for component 'local_mindscape_feed', language 'pt_br'.

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Mindscape Feed';
$string['settings'] = 'Configurações do Mindscape Feed';
$string['itemsperpage'] = 'Itens por página';

// Capability descriptions.
$string['cap:view'] = 'Ver o feed';
$string['cap:post'] = 'Publicar no feed';
$string['cap:comment'] = 'Comentar no feed';
$string['cap:moderate'] = 'Moderar o feed';

// UI strings used in templates.
$string['writepost'] = 'Escreva algo...';
$string['publish'] = 'Publicar';
$string['comment'] = 'Comentar';
$string['nocomments'] = 'Sem comentários ainda.';
$string['delete'] = 'Excluir';
$string['confirmdelete'] = 'Tem certeza que deseja excluir?';
$string['nopostsyet'] = 'Nenhuma postagem encontrada.';
$string['edit'] = 'Editar';
$string['save'] = 'Salvar';
$string['like'] = 'Curtir';
$string['unlike'] = 'Descurtir';

// Tooltip for attachment button.
$string['attachfile'] = 'Anexar arquivo';

// Event and notification strings.
// Name of the event fired when a comment is created.
$string['eventcommentcreated'] = 'Comentário criado no Feed Mindscape';
// Subject of the notification sent to a post author when someone comments on their post.
$string['commentnotificationsubject'] = 'Novo comentário no seu post do Feed Mindscape';
// Plain text notification message. Placeholders: commenter (nome de quem comentou), postcontent (conteúdo do post), url (link para o post).
$string['commentnotificationmessage'] = '{$a->commenter} comentou no seu post: "{$a->postcontent}".\nVeja: {$a->url}';
// HTML notification message. Same placeholders as above; use HTML formatting.
$string['commentnotificationmessagehtml'] = '<p><strong>{$a->commenter}</strong> comentou no seu post:</p><blockquote>{$a->postcontent}</blockquote><p>Veja <a href="{$a->url}">aqui</a>.</p>';

// Event and notification strings for likes.
// Name of the event fired when a like is created.
$string['eventpostliked'] = 'Post curtido no Feed Mindscape';
// Subject of the notification sent to a post author when someone likes their post.
$string['postlikenotificationsubject'] = 'Seu post recebeu uma curtida no Feed Mindscape';
// Plain text notification message for a like. Placeholders: liker (nome de quem curtiu), postcontent (conteúdo do post), url (link para o post).
$string['postlikenotificationmessage'] = '{$a->liker} curtiu seu post: "{$a->postcontent}".\nVeja: {$a->url}';
// HTML notification message for a like. Same placeholders as above; use HTML formatting.
$string['postlikenotificationmessagehtml'] = '<p><strong>{$a->liker}</strong> curtiu seu post:</p><blockquote>{$a->postcontent}</blockquote><p>Veja <a href="{$a->url}">aqui</a>.</p>';

$string['weeklydebates'] = 'Debates da semana';
$string['viewdiscussion'] = 'Ver discussão';
$string['nodebates'] = 'Nenhum debate ativo no momento.';

// Texto do link para a página de debates da semana.
$string['viewdebates'] = 'Ver debates da semana';

// Rótulo do botão usado na página de debates quando uma atividade Kialo está vinculada a um debate.
$string['participatedebate'] = 'Participar do debate';

