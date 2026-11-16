{include file='_head.tpl'}
{include file='_header.tpl'}

<!-- page content -->
<div class="container mt20 offcanvas">
    <div class="row">

        <!-- side panel -->
        <div class="col-md-4 col-lg-3 offcanvas-sidebar js_sticky-sidebar">
            {include file='_sidebar.tpl'}
        </div>
        <!-- side panel -->

        <!-- content panel -->
        <div class="col-md-8 col-lg-9 offcanvas-mainbar">

            <!-- tabs -->
            <div class="content-tabs rounded-sm shadow-sm clearfix">
                <ul>
                    <li {if $view == ""}class="active"{/if}>
                        <a href="{$system['system_url']}/noite">Esta noite</a>
                    </li>
                    <li {if $view == "minhas"}class="active"{/if}>
                        <a href="{$system['system_url']}/noite/minhas">Minhas noites</a>
                    </li>
                    {if $user->_is_admin}
                        <li {if $view == "perguntas"}class="active"{/if}>
                            <a href="{$system['system_url']}/noite/perguntas">Perguntas</a>
                        </li>
                    {/if}
                </ul>
            </div>
            <!-- tabs -->

            {if $view == ""}

                <!-- question -->
                <div class="card">
                    <div class="card-body text-center ptb30">
                        <div class="text-muted small text-uppercase mb10">
                            <i class="fas fa-moon mr5"></i>Pergunta da Noite &middot; {$night_label}
                        </div>
                        {if $question}
                            <div class="h4 mb10">{$question['question']}</div>
                            <div class="text-muted small">
                                {if $answers_count == 0}
                                    Ninguém respondeu ainda. Seja a primeira pessoa!
                                {elseif $answers_count == 1}
                                    1 pessoa já respondeu
                                {else}
                                    {$answers_count} pessoas já responderam
                                {/if}
                                &middot; nova pergunta todo dia às {$night_start_hour}h (horário de Brasília)
                            </div>
                        {else}
                            <div class="h5 mb0">Ainda não há perguntas cadastradas.</div>
                        {/if}
                    </div>
                </div>
                <!-- question -->

                {if $question}
                    {if !$my_answer}
                        <!-- answer form -->
                        <div class="card">
                            <form class="js_ajax-forms" data-url="night/night.php?do=answer">
                                <div class="card-body">
                                    <textarea class="form-control js_autosize" name="answer" rows="3" maxlength="{$max_answer_length}" placeholder="Escreva a sua resposta..."></textarea>
                                    <div class="text-muted small mt10">
                                        <i class="fas fa-lock mr5"></i>Você vê as respostas dos outros depois de enviar a sua. Só dá para responder uma vez por noite.
                                    </div>
                                    <!-- error -->
                                    <div class="alert alert-danger mt10 mb0 x-hidden"></div>
                                    <!-- error -->
                                </div>
                                <div class="card-footer text-right">
                                    <button type="submit" class="btn btn-primary">Responder</button>
                                </div>
                            </form>
                        </div>
                        <!-- answer form -->
                    {/if}

                    {if $can_see_answers}
                        <!-- answers -->
                        <div class="card">
                            <div class="card-header with-icon">
                                <i class="fas fa-comments mr10"></i>Respostas desta noite
                                {if !$my_answer}
                                    <span class="badge badge-warning ml5">visível para você como moderador</span>
                                {/if}
                            </div>
                            <div class="card-body">
                                {if $answers}
                                    {foreach $answers as $_answer}
                                        <div class="d-flex {if !$_answer@last}mb20 pb20 border-bottom{/if}">
                                            <a href="{$system['system_url']}/{$_answer['user_name']}" class="flex-shrink-0 mr10">
                                                <img class="rounded-circle" src="{$_answer['user_picture']}" alt="" width="42" height="42">
                                            </a>
                                            <div class="flex-grow-1">
                                                <div>
                                                    <a class="font-weight-bold" href="{$system['system_url']}/{$_answer['user_name']}">{$_answer['name']}</a>
                                                    {if $_answer['user_verified']}
                                                        <i class="fa fa-check-circle fa-fw verified-badge"></i>
                                                    {/if}
                                                    {if $_answer['is_mine']}
                                                        <span class="badge badge-light ml5">você</span>
                                                    {/if}
                                                    <span class="text-muted small ml5 js_moment" data-time="{$_answer['created_at']}">{$_answer['created_at']}</span>
                                                    {if $night_is_staff}
                                                        <form class="js_ajax-forms d-inline float-right" data-url="night/night.php?do=delete_answer">
                                                            <input type="hidden" name="id" value="{$_answer['answer_id']}">
                                                            <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Apagar resposta" onclick="return confirm('Apagar esta resposta?');">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                            <span class="alert alert-danger x-hidden"></span>
                                                        </form>
                                                    {/if}
                                                </div>
                                                <div class="mt5">{$_answer['answer']|nl2br}</div>
                                            </div>
                                        </div>
                                    {/foreach}
                                {else}
                                    <div class="text-muted text-center">Nenhuma resposta ainda.</div>
                                {/if}
                            </div>
                        </div>
                        <!-- answers -->
                    {/if}
                {/if}

            {elseif $view == "minhas"}

                <!-- archive -->
                <div class="card">
                    <div class="card-header with-icon">
                        <i class="fas fa-book mr10"></i>Minhas noites
                    </div>
                    <div class="card-body">
                        {if $nights}
                            {foreach $nights as $_night}
                                <div class="{if !$_night@last}mb20 pb20 border-bottom{/if}">
                                    <div class="text-muted small mb5"><i class="fas fa-moon mr5"></i>{$_night['night_label']}</div>
                                    <div class="font-weight-bold mb5">{if $_night['question']}{$_night['question']}{else}(pergunta removida){/if}</div>
                                    <div>{$_night['answer']|nl2br}</div>
                                </div>
                            {/foreach}
                        {else}
                            <div class="text-muted text-center">
                                Você ainda não respondeu nenhuma noite.
                                <a href="{$system['system_url']}/noite">Responder a de hoje</a>
                            </div>
                        {/if}
                    </div>
                </div>
                <!-- archive -->

            {elseif $view == "perguntas"}

                <!-- add question -->
                <div class="card">
                    <div class="card-header with-icon">
                        <i class="fas fa-plus-circle mr10"></i>Nova pergunta
                    </div>
                    <form class="js_ajax-forms" data-url="night/night.php?do=add_question">
                        <div class="card-body">
                            <input type="text" class="form-control" name="question" maxlength="{$max_question_length}" placeholder="Ex.: Qual filme você reveria hoje?">
                            <div class="text-muted small mt10">O site sorteia a pergunta de cada noite entre as menos usadas.</div>
                            <!-- error -->
                            <div class="alert alert-danger mt10 mb0 x-hidden"></div>
                            <!-- error -->
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-primary">Adicionar</button>
                        </div>
                    </form>
                </div>
                <!-- add question -->

                <!-- questions -->
                <div class="card">
                    <div class="card-header with-icon">
                        <i class="fas fa-list mr10"></i>Perguntas cadastradas ({count($questions)})
                    </div>
                    <div class="card-body">
                        {if $questions}
                            {foreach $questions as $_question}
                                <div class="d-flex align-items-center {if !$_question@last}mb10 pb10 border-bottom{/if}">
                                    <div class="flex-grow-1">
                                        <div>{$_question['question']}</div>
                                        <div class="text-muted small">
                                            {if $_question['times_used'] > 0}
                                                Usada {$_question['times_used']}x &middot; última vez em {$_question['last_used_label']}
                                            {else}
                                                Ainda não usada
                                            {/if}
                                        </div>
                                    </div>
                                    {if $_question['times_used'] == 0}
                                        <form class="js_ajax-forms ml10" data-url="night/night.php?do=delete_question">
                                            <input type="hidden" name="id" value="{$_question['question_id']}">
                                            <button type="submit" class="btn btn-sm btn-light text-danger" title="Apagar pergunta" onclick="return confirm('Apagar esta pergunta?');">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                            <span class="alert alert-danger x-hidden"></span>
                                        </form>
                                    {/if}
                                </div>
                            {/foreach}
                        {else}
                            <div class="text-muted text-center">Nenhuma pergunta cadastrada.</div>
                        {/if}
                    </div>
                </div>
                <!-- questions -->

            {/if}

        </div>
        <!-- content panel -->

    </div>
</div>
<!-- page content -->

{include file='_footer.tpl'}
