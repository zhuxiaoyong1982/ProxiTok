<?php
require __DIR__ . '/assets.php';
require __DIR__ . '/settings.php';
require __DIR__ . '/following.php';

use Steampixel\Route;
use Helpers\Misc;
use Helpers\Error;
use Views\Models\BaseTemplate;
use Views\Models\FeedTemplate;
use Views\Models\ItemTemplate;

Route::add('/', function () {
    $latte = Misc::latte();
    $latte->render(Misc::getView('home'), new BaseTemplate('Home'));
});

Route::add('/about', function () {
    $latte = Misc::latte();
    $latte->render(Misc::getView('about'), new BaseTemplate('About'));
});

Route::add("/trending", function () {
    $cursor = 0;
    if (isset($_GET['cursor']) && is_numeric($_GET['cursor'])) {
        $cursor = (int) $_GET['cursor'];
    }
	$api = Misc::api();
	$feed = $api->getTrendingFeed($cursor);
	if ($feed->meta->success) {
        $latte = Misc::latte();
		$latte->render(Misc::getView('trending'), new FeedTemplate('Trending', $feed));
	} else {
		Error::show($feed->meta);
	}
});

Route::add("/@([^/]+)", function (string $username) {
    $cursor = 0;
    if (isset($_GET['cursor']) && is_numeric($_GET['cursor'])) {
        $cursor = (int) $_GET['cursor'];
    }
	$api = Misc::api();
	$feed = $api->getUserFeed($username, $cursor);
	if ($feed->meta->success) {
        if ($feed->info->detail->user->privateAccount) {
            http_response_code(400);
            return 'Private account detected! Not supported';
        }
        $latte = Misc::latte();
		$latte->render(Misc::getView('user'), new FeedTemplate($feed->info->detail->user->nickname, $feed));
	} else {
		Error::show($feed->meta);
	}
});

Route::add('/video/([^/]+)', function (string $video_id) {
    $api = Misc::api();
    $item = $api->getVideoByID($video_id);
    if ($item->meta->success) {
        $latte = Misc::latte();
        $latte->render(Misc::getView('video'), new ItemTemplate($item->info->detail->user->nickname, $item));
    } else {
        Error::show($item->meta);
    }
});

Route::add('/music/([^/]+)', function (string $music_id) {
    $cursor = 0;
    if (isset($_GET['cursor']) && is_numeric($_GET['cursor'])) {
        $cursor = (int) $_GET['cursor'];
    }

    $api = Misc::api();
    $feed = $api->getMusicFeed($music_id, $cursor);
	if ($feed->meta->success) {
        $latte = Misc::latte();
		$latte->render(Misc::getView('music'), new FeedTemplate('Music', $feed));
	} else {
		Error::show($feed->meta);
	}
});

Route::add('/tag/(\w+)', function (string $name) {
    $cursor = 0;
    if (isset($_GET['cursor']) && is_numeric($_GET['cursor'])) {
        $cursor = (int) $_GET['cursor'];
    }
	$api = Misc::api();
	$feed = $api->getChallengeFeed($name, $cursor);
	if ($feed->meta->success) {
        $latte = Misc::latte();
		$latte->render(Misc::getView('tag'), new FeedTemplate('Tag', $feed));
	} else {
		Error::show($feed->meta);
	}
});
