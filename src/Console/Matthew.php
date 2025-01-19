<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Friendica\Console;

use Friendica\App;
use Friendica\Core\L10n;
use Friendica\Core\Logger;
use Friendica\Core\PConfig\Capability\IManagePersonalConfigValues;
use Friendica\DI;
use Friendica\Model\APContact;
use Friendica\Model\Item;
use Friendica\Model\User as UserModel;
use Friendica\Model\Verb;
use Friendica\Protocol\Activity;
use Friendica\Protocol\ActivityPub\Transmitter;
use Friendica\Protocol\Relay as ProtocolRelay;
use Friendica\Util\DateTimeFormat;
use RuntimeException;
use Seld\CliPrompt\CliPrompt;

/**
 * tool to control the list of ActivityPub relay servers from the CLI
 *
 * With this script you can access the relay servers of your node from
 * the CLI.
 */
class Matthew extends \Asika\SimpleConsole\Console
{
	protected $helpOptions = ['h', 'help', '?'];

	/**
	 * @var App\Mode
	 */
	private $appMode;
	/**
	 * @var L10n
	 */
	private $l10n;
	/**
	 * @var IManagePersonalConfigValues
	 */
	private $pConfig;


	protected function getHelp()
	{
		$help = <<<HELP
console matthew - Post test messages
Usage
	bin/console matthew post <nickname> <content> [<in-reply-to>] [-h|--help|-?] [-v]

Description
	bin/console matthew post
		Makes a post

Options
    -h|--help|-? Show help information
    -v           Show more debug information.
HELP;
		return $help;
	}

	public function __construct(App\Mode $appMode, L10n $l10n, IManagePersonalConfigValues $pConfig, array $argv = null)
	{
		parent::__construct($argv);

		$this->appMode = $appMode;
		$this->l10n    = $l10n;
		$this->pConfig = $pConfig;
	}

	protected function doExecute(): int
	{
		if ($this->getOption('v')) {
			$this->out('Class: ' . __CLASS__);
			$this->out('Arguments: ' . var_export($this->args, true));
			$this->out('Options: ' . var_export($this->options, true));
		}

		if (count($this->args) == 0) {
			$this->out($this->getHelp());
			return 0;
		}

		if ($this->appMode->isInstall()) {
			throw new RuntimeException('Database isn\'t ready or populated yet');
		}

		$command = $this->getArgument(0);

		switch ($command) {
			case 'post':
				return $this->post();
			case 'like':
				return $this->like();
			default:
				$this->out($this->getHelp());
				return false;
		}
	}

	/**
	 * Retrieves the user nick, either as an argument or from a prompt
	 *
	 * @param int $arg_index Index of the nick argument in the arguments list
	 *
	 * @return string nick of the user
	 * @throws \Friendica\Network\HTTPException\InternalServerErrorException
	 */
	private function getNick($arg_index)
	{
		$nick = $this->getArgument($arg_index);

		if (!$nick) {
			$this->out($this->l10n->t('Enter user nickname: '));
			$nick = CliPrompt::prompt();
			if (empty($nick)) {
				throw new RuntimeException('A nick name must be set.');
			}
		}

		return $nick;
	}

	/**
	 * Retrieves the user from a nick supplied as an argument or from a prompt
	 *
	 * @param int $arg_index Index of the nick argument in the arguments list
	 *
	 * @return array|boolean User record with uid field, or false if user is not found
	 * @throws \Friendica\Network\HTTPException\InternalServerErrorException
	 */
	private function getUserByNick($arg_index)
	{
		$nick = $this->getNick($arg_index);

		$user = UserModel::getByNickname($nick, ['uid']);
		if (empty($user)) {
			throw new RuntimeException($this->l10n->t('User not found'));
		}

		return $user;
	}

	/**
	 * Makes a post for a user
	 *
	 * @return int Return code of this command
	 *
	 * @throws \Exception
	 */
	private function post()
	{
		$user = $this->getUserByNick(1);

		$content = $this->getArgument(2);

		if (is_null($content)) {
			$this->out($this->l10n->t('Enter content: '));
			$content = CliPrompt::prompt();
			if (empty($content)) {
				throw new RuntimeException('Content must be provided.');
			}
		}
		$inReplyTo = $this->getArgument(3);

        $post = ['uid' => $user['uid']];
        $post = DI::contentItem()->initializePost($post);
        $post['body'] = $content;
        if (!is_null($inReplyTo)) {
            $item = Item::fetchByLink($inReplyTo);
            if ($item == 0) {
                $this->out('Could not find item ' . $inReplyTo);
                return 1;
            }
            $post['thr-parent'] = $inReplyTo;
            $post['gravity']     = Item::GRAVITY_COMMENT;
        }

        $post_id = Item::insert($post);
        if ($post_id == 0) {
            $this->out('Failed to insert post');
            return 1;
        }
        $this->out($post['uri']);
		return 0;
	}

	/**
	 * Makes a like for a user
	 *
	 * @return int Return code of this command
	 *
	 * @throws \Exception
	 */
	private function like()
	{
		$user = $this->getUserByNick(1);

		$inReplyTo = $this->getArgument(2);

        $post = ['uid' => $user['uid']];
        $post = DI::contentItem()->initializePost($post);
        if (!is_null($inReplyTo)) {
            $item = Item::fetchByLink($inReplyTo);
            if ($item == 0) {
                $this->out('Could not find item');
                return 1;
            }
            $post['thr-parent'] = $inReplyTo;
            $post['gravity']     = Item::GRAVITY_ACTIVITY;
            $post['verb'] = Activity::LIKE;
            $post['body'] = Activity::LIKE;
        }

        $post_id = Item::insert($post);
        if ($post_id == 0) {
            $this->out('Failed to insert post');
            return 1;
        }
        $this->out($post['uri']);
		return 0;
	}
}
