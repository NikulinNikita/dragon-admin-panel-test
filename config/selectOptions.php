<?php

return [
    'common' => [
        'adminPagination' => 20,
        'date'            => 'Y-m-d',
        'dateTime'        => 'Y-m-d H:i',
        'dateTimeDB'      => 'Y-m-d H:i:s',
        'time'            => 'H:i',
        'status'          => ['active', 'inactive'],
        'gender'          => ['male', 'female'],
        'seats'           => [1, 2, 3, 5, 6, 7, 8],
        'locales'         => ['ru', 'en', 'zh', 'ko']
    ],

    'users' => [
        'verification' => ['not_verified', 'verified'],
        'loyalty_user' => ['vip', 'regular'],
        'blocked'      => ['no', 'withdraw', 'login', 'gameplay', 'withdraw_and_gameplay', 'personal_manager_only'],
    ],

    'roles' => [
        'type' => ['staff', 'user'],
    ],

    'deposit_requests' => [
        'status' => [
            'new',
            'canceled_by_user',
            'succeed',
            'declined',
            'approved',
            'approved_to_proceed',
            'sent_to_recheck_to_manager',
            'sent_to_recheck_to_operator'
        ],
    ],

    'withdrawal_requests' => [
        'gateway' => ['bank_transfer', 'card', 'wechat'],
        'status'  => [
            'new',
            'canceled_by_user',
            'succeed',
            'declined',
            'approved',
            'approved_to_proceed',
            'sent_to_recheck_to_manager',
            'sent_to_recheck_to_operator'
        ],
    ],

    'bonuses' => [
        'target_till' => ['any', 'money', 'bonus']
    ],

    'user_bonuses' => [
        'status' => ['active', 'inactive', 'canceled', 'applied'],
    ],

    'operations' => [
        'operatable_type' => [
            'deposit_request',
            'withdrawal_request',
            'baccarat_bet',
            'roulette_bet',
            'user_bonus',
            'agents_reward',
            'subagents_reward',
            'user_status_reward'
        ],
        'action'          => ['add_money', 'open_special_table'],
    ],

    'authorizations' => [
        'source' => ['desktop', 'tablet', 'mobile'],
    ],

    'settings' => [
        'type' => ['bonuses', 'payments', 'users', 'agents', 'tables', 'game_statistics', 'chats', 'misc'],
    ],

    'agents' => [
        'type' => ['win', 'deposit'],
    ],

    'agent_links' => [
        'status' => ['unused', 'used'],
    ],

    'bank_account_operations' => [
        'operatable_type' => ['deposit_request', 'withdrawal_request', 'internal_operations'],
    ],

    'user_banks' => [
        'status' => ['active', 'inactive', 'deleted_by_user']
    ],

    'user_bank_accounts' => [
        'status' => ['active', 'inactive', 'deleted_by_user']
    ],

    'currencies' => [
        'fmt_symbol_placement' => ['before', 'after'],
        'delimiter'            => ['.', ',', 'space'],
    ],

    'baccarat_rounds' => [
        'status' => ['started', 'finished', 'aborted', 'failed']
    ],

    'baccarat_bets' => [
        'status' => ['playing', 'won', 'lost', 'stay']
    ],

    'baccarat_shoes' => [
        'status' => ['opened', 'closed']
    ],

    'baccarat_results' => [
        'code' => ['player', 'banker', 'tie', 'player-pair', 'banker-pair', 'big', 'small', 'player-dragon', 'banker-dragon']
    ],

    'roulette_rounds' => [
        'status' => ['started', 'finished', 'aborted', 'failed']
    ],

    'roulette_cells' => [
        'color' => ['red', 'black', 'green']
    ],

    'roulette_bets' => [
        'status' => ['playing', 'won', 'lost']
    ],

    'roulette_results' => [
        'code' => [
            'straight',
            'split',
            'street',
            'corner',
            'six-line',
            'first-four',
            'red',
            'black',
            'odd',
            'even',
            'low',
            'high',
            'column1',
            'column2',
            'column3',
            'dozen1',
            'dozen2',
            'dozen3'
        ],
    ],

    'user_status_changes' => [
        'status' => ['up', 'down']
    ],

    'bet_types' => ['baccarat_bet', 'roulette_bet'],

    'risk_events' => [
        'source' => ['internal', 'external'],
        'status' => ['pending', 'review', 'processed']
    ],

    'risk_event_staff_actions' => [
        'status' => ['pending', 'review', 'processed']
    ],

    'riskables' => [
        'riskable_type' => [
            'user',
            'staff',
            'baccarat_bet',
            'roulette_bet',
            'deposit_request',
            'withdrawal_request',
            'game',
            'user_session',
            'user_bonus'
        ]
    ],

    'internal_operations' => [
        'type' => ['deposit', 'withdrawal', 'transfer', 'commission', 'other']
    ],

    'result_limit_currencies' => [
        'limitable_type' => ['baccarat_result', 'roulette_result']
    ],

    'regions' => [
        'blocked' => ['no', 'yes']
    ],

    'agent_rewards' => [
        'type'   => ['agent', 'subagent'],
        'status' => ['pending', 'paid', 'cancelled']
    ],

    'bonus_rewards' => [
        'status' => ['canceled' => 'canceled', 'pending' => 'pending', 'active' => 'active', 'paid' => 'paid']
    ],

    'loop_command_events' => [
        'status' => ['processed', 'not_processed', 'in_processed']
    ],

    'bets_bank_accruals' => [
        'roundable_type' => ['baccarat_round', 'roulette_round']
    ]
];
