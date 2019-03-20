<template>
    <div v-if="selectedConversation" class="b-mc_chat">
        <span>{{ conversations.length }}</span>
        <div class="container clearfix">
            <div class="people-list col-md-3" id="people-list">
                <div class="search">
                    <input v-model="searchConversation" type="text" placeholder="search"/>
                    <a @click.prevent="getConversationMessages('', {page: 1})" href="#"><i class="fa fa-search"></i></a>
                </div>
                <ul class="list">
                    <li class="clearfix" :class="{'active': conversation.id === selectedConversation.id}" v-for="conversation in conversations">
                        <div class="about">
                            <a @click.prevent="setActiveConversation(conversation)" href="#">
                                <div class="name">Conversation #{{ conversation.id }}</div>
                                <div class="status">
                                    <i class="fa fa-circle" :class="[conversation.status === 'active' ? 'online' : 'offline']"></i>
                                    <span class="updated">Last update: {{ conversation.updated_at }}</span>
                                </div>
                            </a>
                        </div>
                    </li>
                </ul>
                <pagination v-if="pagination.conversations.last_page > 1" :pagination="pagination.conversations" :offset="3"
                            @paginate="(page) => getConversationMessages(selectedConversation.id, {page:page, scroll: true})"></pagination>
            </div>

            <div class="chat col-md-9">
                <div class="chat-header clearfix">
                    <div class="chat-about">
                        <div class="chat-with">Conversation #{{ selectedConversation ? selectedConversation.id : 0 }}</div>
                        <div class="chat-num-messages">Totally <b>{{ isset(selectedConversation, 'messages.length', 0) }}</b> messages</div>
                    </div>
                    <!--<i class="fa fa-star"></i>-->
                </div> <!-- end chat-header -->

                <div class="chat-history" id="scrollToContainer">
                    <ul>
                        <li class="clearfix" v-for="(message, k) in selectedConversation.messages">
                            <div class="message-data" :class="{'align-right': message.user.name === 'admin'}">
                                <span class="message-data-name"><b>{{ message.user.name }}</b></span>
                                <span class="message-data-time">{{ message.created_at }}</span>
                            </div>
                            <a v-if="message.user.name !== 'admin' && !message.user.blocked_chat_until"
                               @click.prevent="blockUser(message.user.id, {showAlert: true})" href="#" title="Block User"><i class="fa fa-remove"></i></a>
                            <div class="message" :class="[message.user.name === 'admin' ? 'other-message float-right' : 'my-message',
                            {'blocked-message': message.status === 'inactive'}]">
                                {{ message.body }}
                            </div>
                            <a v-if="message.status === 'active'" @click.prevent="deleteMessage(message.id, {showAlert: true})" href="#" title="Delete Message">
                                <i class="fa fa-trash-o"></i></a>
                        </li>
                    </ul>
                </div> <!-- end chat-history -->

                <div class="chat-message clearfix">
                    <div class="col-md-11">
                        <textarea v-model="newMessage" name="message-to-send" id="message-to-send" placeholder="Type your message" rows="3"></textarea>
                    </div>
                    <div class="col-md-1">
                        <button @click.prevent="sendMessage(selectedConversation.id)">Send</button>
                    </div>
                </div> <!-- end chat-message -->
            </div> <!-- end chat -->
        </div> <!-- end container -->
    </div> <!-- end b-mc_chat -->
</template>

<script>
    import axios from 'axios';
    import DefaultComponent from '../Default/Default.vue';
    import {initSocketConnection} from '../../src/SocketConnection'
    import {showConfirmAlert} from '../../src/SweetAlert'
    import {generateVueUri, scrollToContainer} from '../../src/DefaultFunctions'
    import './McChats.css';

    export default {
        props:
            ['conversations', 'selected_conversation', 'pagination'],

        data() {
            return {
                connection: null,
                conversations: this.conversations,
                selectedConversation: this.selected_conversation,
                pagination: this.pagination,
                newMessage: '',
                searchConversation: '',
            }
        },

        mounted() {
            scrollToContainer(this);
            initSocketConnection(this);
            this.setActiveConversation(this.selected_conversation);
        },

        methods: {
            ...DefaultComponent.methods,

            async setActiveConversation(conversation) {
                await this.getConversationMessages(conversation.id, {scroll: true});
                await this.connectToSockets(conversation.id);
            },

            async connectToSockets(id) {
                if (this.connection) {
                    this.connection.channel('mc-chat-conversation.' + id)
                        .listen('\\Musonza\\Chat\\Eventing\\MessageWasSent', (e) => {
                            let message = e.message;

                            message.user = {name: message.sender.name, id: message.sender.id};

                            this.selectedConversation.messages.push(message);
                        });
                }
            },

            async getConversationMessages(id, params = {}) {
                params.title = this.searchConversation;
                params.page = params.page ? params.page : this.isset(this, 'pagination.conversations.current_page', 1);

                await axios.get(generateVueUri(`mc_chats/getSectionData/${id}`, params))
                    .then((response) => {
                        let {data, pagination} = response.data;
                        let {conversations, selectedConversation} = data;

                        this.conversations = conversations.data;
                        this.selectedConversation = selectedConversation;
                        this.pagination = pagination;
                    })
                    .catch((error) => {
                        Admin.Messages.message('Error!', error.response.data.error ? error.response.data.error : error.message, 'error');
                    });
                if (params.scroll)
                    await scrollToContainer(this);
            },

            async sendMessage(id, params = {}) {
                await axios.post(generateVueUri(`mc_chats/sendMessage/${id}`), {newMessage: this.newMessage})
                    .then((response) => {
                        console.log(response.data);
                    })
                    .catch((error) => {
                        Admin.Messages.message('Error!', error.response.data.error ? error.response.data.error : error.message, 'error');
                    });
                await this.getConversationMessages(this.selectedConversation.id, {scroll: true});
                this.newMessage = '';
            },

            async blockUser(id, params = {}) {
                if (params.showAlert) {
                    showConfirmAlert(this.blockUser, id);
                    return true;
                }

                await axios.get(`/admin_panel/vue/mc_chats/blockUser/${id}`)
                    .then((response) => {
                        if (response.data.success)
                            Admin.Messages.message('Success', `User successfully blocked until ${response.data.blocked_chat_until} !`, 'success');
                    })
                    .catch((error) => {
                        Admin.Messages.message('Error!', error.response.data.error ? error.response.data.error : error.message, 'error');
                    });
                await this.getConversationMessages(this.selectedConversation.id);
            },

            async deleteMessage(id, params = {}) {
                if (params.showAlert) {
                    showConfirmAlert(this.deleteMessage, id);
                    return true;
                }

                await axios.get(`/admin_panel/vue/mc_chats/deleteMessage/${id}`)
                    .then((response) => {
                        if (response.data.success)
                            Admin.Messages.message('Success', 'Message successfully deleted!', 'success');
                    })
                    .catch((error) => {
                        Admin.Messages.message('Error!', error.response.data.error ? error.response.data.error : error.message, 'error');
                    });
                await this.getConversationMessages(this.selectedConversation.id);
            },
        }
    };
</script>