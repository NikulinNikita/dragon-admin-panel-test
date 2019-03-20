<mc-chats
		:conversations="{{ collect($data['conversations']->items()) ?? collect() }}"
		:selected_conversation="{{ $data['selectedConversation'] ?? collect() }}"
		:pagination="{{ collect($pagination) ?? collect() }}"
></mc-chats>