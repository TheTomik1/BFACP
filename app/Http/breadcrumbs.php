<?php

use BFACP\Account\Role;
use BFACP\Facades\Macros;
use Illuminate\Support\Facades\Cache;

Breadcrumbs::register('home', function ($b) {
    $b->push(trans('navigation.main.items.dashboard.title'), route('home'), [
        'icon' => Macros::faicon(trans('navigation.main.items.dashboard.icon.fa')),
    ]);
});

Breadcrumbs::register('servers.list', function ($b) {
    $b->parent('home');
    $b->push(trans('navigation.main.items.servers.list.title'), route('servers.list'), [
        'icon' => Macros::faicon(trans('navigation.main.items.servers.list.icon.fa')),
    ]);
});

Breadcrumbs::register('servers.live', function ($b) {
    $b->parent('servers.list');
    $b->push(trans('navigation.main.items.servers.scoreboard.title'), route('servers.live'), [
        'icon' => Macros::faicon(trans('navigation.main.items.servers.scoreboard.icon.fa')),
    ]);
});

Breadcrumbs::register('servers.show', function ($b, $server) {
    $b->parent('servers.list');
    $b->push($server->ServerName);
});

Breadcrumbs::register('chatlog.search', function ($b) {
    $b->parent('home');
    $b->push(trans('navigation.main.items.chatlogs.title'), route('chatlog.search'), [
        'icon' => Macros::faicon(trans('navigation.main.items.chatlogs.icon.fa')),
    ]);
});

Breadcrumbs::register('battlereport.search', function($b) {
    $b->parent('home');
    $b->push(trans('navigation.main.items.battlereports.title'), route('battlereport.search'), [
        'icon' => Macros::faicon(trans('navigation.main.items.battlereports.icon.fa')),
    ]);
});

Breadcrumbs::register('playerdisconnects.search', function($b) {
    $b->parent('home');
    $b->push(trans('navigation.main.items.playerdisconnects.title'), route('playerdisconnects.search'), [
        'icon' => Macros::faicon(trans('navigation.main.items.playerdisconnects.icon.fa')),
    ]);
});

Breadcrumbs::register('player.listing', function ($b) {
    $b->parent('home');
    $b->push(trans('navigation.main.items.playerlist.title'), route('player.listing'), [
        'icon' => Macros::faicon(trans('navigation.main.items.playerlist.icon.fa')),
    ]);
});

Breadcrumbs::register('player.show', function ($b, $id, $name = null) {
    $b->parent('player.listing');

    if (empty($name)) {
        if (Cache::has(sprintf('player.%u', $id))) {
            $player = Cache::get(sprintf('player.%u', $id));
            $b->push($player->SoldierName);
        } else {
            $b->push(sprintf('#%u', $id));
        }
    } else {
        $b->push($name);
    }
});

Breadcrumbs::register('admin.adkats', function ($b) {
    $b->parent('home');
    $b->push(trans('navigation.admin.adkats.title'), null);
});

Breadcrumbs::register('admin.site', function ($b) {
    $b->parent('home');
    $b->push(trans('navigation.admin.site.title'), null);
});

/*===================================
=            Adkats Bans            =
===================================*/

Breadcrumbs::register('admin.adkats.bans.index', function ($b) {
    $b->parent('admin.adkats');
    $b->push(trans('navigation.admin.adkats.items.banlist.title'), route('admin.adkats.bans.index'), [
        'icon' => Macros::ionicon(trans('navigation.admin.adkats.items.banlist.icon.ion')),
    ]);
});

Breadcrumbs::register('admin.adkats.bans.edit', function ($b, $id) {
    $b->parent('admin.adkats.bans.index');
    $b->push(trans('navigation.admin.adkats.items.banlist.items.edit.title', ['id' => $id]));
});

/*====================================
=            Adkats Users            =
====================================*/

Breadcrumbs::register('admin.adkats.users.index', function ($b) {
    $b->parent('admin.adkats');
    $b->push(trans('navigation.admin.adkats.items.users.title'), route('admin.adkats.users.index'), [
        'icon' => Macros::faicon(trans('navigation.admin.adkats.items.users.icon.fa')),
    ]);
});

Breadcrumbs::register('admin.adkats.users.edit', function ($b, $id) {
    $b->parent('admin.adkats.users.index');
    $b->push(trans('navigation.admin.adkats.items.users.items.edit.title', ['id' => $id]));
});

/*====================================
=            Adkats Roles            =
====================================*/

Breadcrumbs::register('admin.adkats.roles.index', function ($b) {
    $b->parent('admin.adkats');
    $b->push(trans('navigation.admin.adkats.items.roles.title'), route('admin.adkats.roles.index'), [
        'icon' => Macros::faicon(trans('navigation.admin.adkats.items.roles.icon.fa')),
    ]);
});

Breadcrumbs::register('admin.adkats.roles.edit', function ($b) {
    $b->parent('admin.adkats.roles.index');
    $b->push(trans('navigation.admin.adkats.items.roles.items.edit.title'));
});

Breadcrumbs::register('admin.adkats.roles.create', function ($b) {
    $b->parent('admin.adkats.roles.index');
    $b->push(trans('navigation.admin.adkats.items.roles.items.create.title'));
});

/*==============================================
=            Adkats Special Players            =
==============================================*/

Breadcrumbs::register('admin.adkats.special_players.index', function ($b) {
    $b->parent('admin.adkats');
    $b->push(trans('navigation.admin.adkats.items.special_players.title'),
        route('admin.adkats.special_players.index'), [
            'icon' => Macros::faicon(trans('navigation.admin.adkats.items.special_players.icon.fa')),
        ]);
});

/*==================================
=            Site Users            =
==================================*/

Breadcrumbs::register('admin.site.users.index', function ($b) {
    $b->parent('admin.site');
    $b->push(trans('navigation.admin.site.items.users.title'), route('admin.site.users.index'), [
        'icon' => Macros::faicon(trans('navigation.admin.site.items.users.icon.fa')),
    ]);
});

Breadcrumbs::register('admin.site.users.edit', function ($b, $id) {
    $b->parent('admin.site.users.index');
    $b->push(trans('navigation.admin.site.items.users.items.edit.title', ['id' => $id]));
});

/*==================================
=            Site Roles            =
==================================*/

Breadcrumbs::register('admin.site.roles.index', function ($b) {
    $b->parent('admin.site');
    $b->push(trans('navigation.admin.site.items.roles.title'), route('admin.site.roles.index'), [
        'icon' => Macros::faicon(trans('navigation.admin.site.items.roles.icon.fa')),
    ]);
});

Breadcrumbs::register('admin.site.roles.edit', function ($b, $id) {
    $b->parent('admin.site.roles.index');
    $b->push(trans('navigation.admin.site.items.roles.items.edit.title', ['name' => Role::find($id)->name]));
});

/*=====================================
=            Site Settings            =
=====================================*/

Breadcrumbs::register('admin.site.settings.index', function ($b) {
    $b->parent('admin.site');
    $b->push(trans('navigation.admin.site.items.settings.title'), route('admin.site.settings.index'), [
        'icon' => Macros::faicon(trans('navigation.admin.site.items.settings.icon.fa')),
    ]);
});

/*====================================
=            Site Updater            =
====================================*/

Breadcrumbs::register('admin.updater.index', function ($b) {
    $b->parent('admin.site');
    $b->push(trans('navigation.admin.site.items.updater.title'), route('admin.updater.index'), [
        'icon' => Macros::faicon(trans('navigation.admin.site.items.updater.icon.fa')),
    ]);
});

/*====================================
=            Site Servers            =
====================================*/

Breadcrumbs::register('admin.site.servers.index', function ($b) {
    $b->parent('admin.site');
    $b->push(trans('navigation.admin.site.items.servers.title'), route('admin.site.servers.index'), [
        'icon' => Macros::faicon(trans('navigation.admin.site.items.servers.icon.fa')),
    ]);
});

Breadcrumbs::register('admin.site.servers.edit', function ($b, $server) {
    $b->parent('admin.site.servers.index');
    $b->push($server->ServerName);
});
