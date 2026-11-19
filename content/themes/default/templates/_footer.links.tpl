<!-- footer links -->
<div class="container">
	<div class="row footer {if $page == 'index' && !$user->_logged_in}border-top-0{/if}">
		<div class="col-sm-6 dropdown">
			<span class="mr5">&copy; {'Y'|date} {$system['system_title']}</span>
		</div>

		<div class="col-sm-6 links">
			{if $static_pages}
				{foreach $static_pages as $static_page}
					<a {if !$static_page@first}class="pl10"{/if} href="{$system['system_url']}/static/{$static_page['page_url']}">
						{__($static_page['page_title'])}
					</a>
				{/foreach}
			{/if}
			{if $system['contact_enabled']}
				<a class="pl10" href="{$system['system_url']}/contacts">
					{__("Contact Us")}
				</a>
			{/if}
			{if $system['directory_enabled']}
				<a class="pl10" href="{$system['system_url']}/directory">
					{__("Directory")}
				</a>
			{/if}
		</div>
	</div>
</div>
<!-- footer links -->