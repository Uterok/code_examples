<template>
	<section class="chat-section pt-5">
		<div v-show="data_loaded">
			<div class="row justify-content-center align-items-center filter-group px-3 mb-4">
				<div class="col-lg-5">
					<a href="##" class="px-5" :class="{active: filter == activeFilter}" v-for="(filter, index) in filters" @click="sortByType(filter)">{{filter}}</a>
				</div>
				<div class="col-lg-6 input-group select-group py-2">
					<input type="text" class="form-control" placeholder="Enter the name of user key words" v-model="form.search">
					<div class="input-group-prepend">
						<div class="input-group-text pr-4">
							<i class="fa fa-search text-dark-easy"></i>
						</div>
					</div>
				</div>
			</div>

			<transition name="fade" mode="out-in">
				<!-- Full chat -->
				<div class="list-card chat-box main-chat-box chat-height easy-shadow mb-0" key="full" v-if="active_thread_index == null">
					<div class="message-card d-flex br-dark-easy" :class="{'border-bottom': index !== threads.length - 1 }" v-for="(thread, index) in threads">

						<div class="col-lg-3 d-flex justify-content-between align-items-center px-4 py-3 border-right br-dark-easy">
							<div class="d-flex align-items-center">
								<input type="checkbox">
								<img :src="$root.userImage(thread.collocutors ? thread.collocutors[0] : '')" alt="" width="60" height="60" class="mx-3 bg-secondary rounded-circle" @click="openChat(index, thread)">
								<h5 class="mb-0 mr-2" @click="openChat(index, thread)">{{thread.collocutors ? thread.collocutors[0].name : ''}}</h5>
							</div>
							<span class="status bg-secondary" @click="openChat(index, thread)"  v-if="isCollocutorsActive(thread)"></span>
						</div>

						<div class="col-lg-7 d-flex align-items-center py-3 pl-lg-5" @click="openChat(index, thread)">
							<p class="mb-0">{{$options.filters.truncate(threadLastMsgShow(thread))}}</p>
						</div>

						<div class="col-lg-2 d-flex align-items-center py-3" :class="[(thread.new_msgs ? thread.new_msgs.length : 0) > 0 ? 'justify-content-between' : 'justify-content-end']" @click="openChat(index, thread)">
							<span class="btn bg-easy rounded-circle px-2 text-white" v-if="(thread.new_msgs ? thread.new_msgs.length : 0) > 0">{{(thread.new_msgs ? thread.new_msgs.length : 0)}}</span>
							<p class="mb-0">{{thread.last_message ? thread.last_message.created_at_formatted_time : ''}}</p>
						</div>

					</div>
				</div>

				<!-- Detail chat -->
				<div class="list-card row chat-height flex-row easy-shadow p-0 mb-0" key="detail" v-else>
					<div class="col-lg-5 chat-box px-0">
						<div class="message-card d-flex br-dark-easy py-3" :class="{'border-bottom': index !== threads.length - 1, 'state-message': active_thread_index == index }" v-for="(thread, index) in threads">

							<div class="col-lg-10 d-flex align-items-center pl-4">
								<div class="d-flex align-items-center mr-3">
									<input type="checkbox">
									<img :src="$root.userImage(thread.collocutors ? thread.collocutors[0] : '')" alt="" width="60" height="60" class="ml-3 bg-secondary rounded-circle" @click="openChat(index, thread)">
								</div>
								<div class="d-flex flex-column justify-content-between w-100" @click="openChat(index, thread)">
									<div class="d-flex align-items-center">
										<h5 class="mb-2 mr-3">{{thread.collocutors ? thread.collocutors[0].name : ''}}</h5>
										<span class="status" :class="[isCollocutorsActive(thread) ? 'bg-green' : 'bg-dark-easy']" v-if="isCollocutorsActive(thread)"></span>
									</div>
									<p class="mb-0 text-dark-easy">{{$options.filters.truncate(threadLastMsgShow(thread))}}</p>
								</div>
							</div>

							<div class="col-lg-2 d-flex flex-column justify-content-between align-items-center" @click="openChat(index,thread)">
								<p class="mb-1 text-dark-easy">{{thread.last_message ? thread.last_message.created_at_formatted_time : ''}}</p>
								<span class="btn bg-easy rounded-circle px-2 text-white" v-if="(thread.new_msgs ? thread.new_msgs.length : 0) > 0">{{(thread.new_msgs ? thread.new_msgs.length : 0)}}</span>
							</div>

						</div>
					</div>
					<div id="msgbox" class="col-lg-7 px-0" v-show="threads.length">
						<div class="px-5 easy-shadow" id="userInfo">
							<div class="d-flex justify-content-between py-4 chat-header">
								<div class="d-flex align-items-center">
									<div class="d-flex align-items-center mr-3" v-for="member in active_collocutors">
										<img :src="$root.userImage(member)" alt="" width="35" height="35" class="bg-secondary rounded-circle">
										<h5 class="mb-0 mx-2">{{member.name}}</h5>
										<span class="status bg-green"></span>
									</div>
								</div>
								<img src="/images/platform/icons/vertical_dots.svg" alt="">
							</div>
						</div>
							<div class="header-space"></div>
						<sync-loader color="#808080" v-show="isMessagesLoading"></sync-loader>
						<div class="d-flex justify-content-center">
							<a role="button" class="py-3 text-uppercase w-100 pointer text-center" @click="loadMoreThreadMessages" v-show="!isMessagesLoading && hasUnloadMsgs">
								more
							</a>
						</div>
					</center>
					<div :id="getMessageElementId(message)" v-for="(message, index) in activeThreadMsgsList" v-if="activeThreadMsgsList != null">
						<div v-if="activeThread.new_first_msg_index === index">
							<div class="d-flex justify-content-center">
								<h5 class="mb-0 text-uppercase w-100 text-center">NEW MESSAGES</h5>
							</div>
							<hr size="10" color="#000000">
						</div>
							<div class="open-message-card message-card d-flex flex-column border-dark py-3 w-100 px-5"
							:class="messageStyle(message)">
							<div class="d-flex align-items-center mb-2" v-if="isNewDateTimeInMessage(activeThreadMsgsList, index)">
								<img :src="$root.userImage(message.user_from)" alt="" width="35" height="35" class="bg-secondary rounded-circle">
								<h5 class="mb-0 mx-2">{{message.user_from.name}}</h5>
								<span class="text-dark-easy">{{message.created_at_formatted_time}}</span>
								&nbsp;
								<span class="status bg-secondary" @click="openChat(index, thread)"  v-if="isCollocutorsActive(activeThread) && (auth.id != message.user_id_from)"></span>
							</div>
							<div class="px-4 py-2 message-content text" :class="[message.is_auth_user_message ? 'sent-msg' : 'received-msg']">
								<div v-for="attachment in message.attachments">
									<a :href="attachment.link" :download="attachment.real_name">
										<i :class="getAttachmentIconName(attachment.type)" aria-hidden="true"></i>&nbsp;{{attachment.real_name}}
									</a>
									<br>
									<div height="150" max-width="230">
										<a :href="attachment.link" target="_blank" v-if="isShowAttachmentPreview(attachment.type)">
											<img :src="attachment.link" width="100%" height="100%" v-if="attachment.type == 'image'">
											<video width="100%" :src="attachment.link" autoplay loop muted v-else></video>
										</a>
									</div>
									<br>
								</div>
								{{message.message}}
							</div>
						</div>
					</div>

					<span v-if="activeThread.is_active">{{activeThread.is_active.name}} is typing...</span>
					<div class="py-4 px-5 border-top br-light-easy d-flex justify-content-between align-items-center chat-line" id="chat-line">
						<input id="msginput" class="form-control ph-dark-easy border-0" type="text" v-model="form.message" placeholder="Type your Message..." ref="messageInput" @focus="setReadNewMessages" @input="onMessageInput(activeThread)" @keyup.enter="send" @keydown="onTyping">
						<a role="button" class="mr-3">
							<img src="/images/platform/icons/image.svg" alt="" width="21">
						</a>
						<a role="button"  @click="toggleAttachmentDropzone">
							<img src="/images/platform/icons/paperclip.svg" alt="" width="21">
						</a>
						<!-- <a role="button" @click="send"><i class="fa fa-paper-plane-o send-icon"></i></a> -->
					</div>
					<div class="px-5 pb-5" v-show="is_show_attachment_dropzone">
						<vue-dropzone
						class=" platform-input text-center dropzone"
						ref="AttachDropzone"
						id="dzattach"
						:options="attachDzOptions"
						v-on:vdropzone-error="attachDzError"
						v-on:vdropzone-success="atatchDzSuccess">

					</vue-dropzone>
				</div>
			</div>
		</div>
	</transition>
		</div>
		<div v-show="!data_loaded">
			<sync-loader color="#808080"></sync-loader>
		</div>
	</section>	
</template>

<script>
	import striptags from 'striptags'
	import { required } from 'vuelidate/lib/validators'
	import vSelect from 'vue-select'
	import vue2Dropzone from 'vue2-dropzone'
	import SyncLoader from 'vue-spinner/src/RiseLoader.vue'

	export default {
		components: {
			vSelect,
			'vue-dropzone': vue2Dropzone,
			SyncLoader,
		},

		props: ['auth'],

		data() {
			return {
				active_collocutors: [],
				active_thread_index: null,
				is_show_attachment_dropzone: false,
				data_loaded: false,
				messages_loading: false,
				scroll_to_msgbox_id_on_update: null,
				threads: [],
				filters: ['all', 'archived', 'starred'],
				activeFilter: 'all',
				form: {},
				users: [],
				new_chat_user: null,
				new_thread_subject: null,
				attachDzOptions: {
					method: 'POST',
					addRemoveLinks: true,
					url: `${this.$root.files_upload_url}/messages`,
					headers: {
						delete: false
					},
					dictDefaultMessage: "Drop file here to attach"
				},
				msgbox: null,
			}
		},

		watch: { 
			skill: function(newVal, oldVal) {
				this.$refs.messageInput.focus()
			},
			active_thread_index() {
				if(this.active_thread_index !== null) {
					this.timer = setTimeout(() => {
						let chatWidth = $('#msgbox').css("width");
						chatWidth = chatWidth.split("px");
						chatWidth = parseInt(chatWidth);
						$('#chat-line').css({
							'width': chatWidth
						});
						$('#userInfo').css({
							'width': chatWidth
						})
						this.scrollChat()
					}, 200);
				}
			}
		},

		filters: {
			truncate: function(value) {
				let text = striptags(value)
				if (!text) return ''
					text = text.toString()
				if(text.length >= 105) {
					return text.split(" ").splice(0, 13).join(" ") + ' ...';
				} else {
					return text
				}
			}
		},

		validations: {
			form: {
				message: {
					required
				}
			}
		},

		computed: {
			activeThread() {
				return (this.active_thread_index != null) && (this.threads && this.threads.length) ? 
				this.threads[this.active_thread_index] : 
				null;
			},
			activeThreadMsgsList() {
				return (this.activeThread && this.activeThread.messages && this.activeThread.messages.length) ? 
				this.activeThread.messages : 
				null;
			},
			isMessagesLoading() {
				return this.messages_loading;
			},
			//is active thread has unload messages
			hasUnloadMsgs() {
				let thread = this.activeThread;
				return thread.msgs_count && (thread.loaded_msgs_count < thread.msgs_count);
			},
		},

		mounted() {

		},

		methods: {
			//DROPZONE EVENTS
			atatchDzSuccess(file, response) {
				if (!this.form.attached_files) {
					this.form.attached_files = [];
				}
				this.form.attached_files.push(response);
			},
			attachDzError(file, message, xhr) {
				
			},
			toggleAttachmentDropzone() {
				this.is_show_attachment_dropzone = !this.is_show_attachment_dropzone;
				if (!this.is_show_attachment_dropzone) {
					this.$refs.AttachDropzone.removeAllFiles();
				} else {
					this.scroll_to_msgbox_id_on_update = 'dzattach';
				}

				this.scrollChat()
			},

			scrollChat() {
				let box = document.getElementById("msgbox")
				let isScrolledToBottom = box.scrollHeight - box.clientHeight <= box.scrollTop + 1

				setTimeout(function() {
					box.scrollTop = box.scrollHeight
				}, 0)
			},

			//CHAT METHODS
			openChat(index, item) {
				this.active_collocutors = item.collocutors

				this.active_thread_index = index;
				if (this.$refs.messageInput) {
					this.$refs.messageInput.focus()
				}

				if (!item.loaded_msgs_count && item.msgs_count) {
					this.messages_loading = true;
					axios.get(`${this.$root.default_api_prefix}/threads/${item.id}/messages`)
					.then(response => {
						let messages = response.data;
						item.messages = [].concat(messages);
						Vue.set(item, 'loaded_msgs_count', messages.length);
						this.messages_loading = false;
						this.scrollChat()
					})
					.catch(error => {

					});
				}

				if (item.new_msgs.length) {

				}
			},
			loadMoreThreadMessages() {
				if (this.hasUnloadMsgs && !this.messages_loading) {
					this.messages_loading = true;
					let thread = this.activeThread;
					//get last loaded(furst in array) message
					let last_loaded_msg_id = thread.messages[0].id;
					axios.get(`${this.$root.default_api_prefix}/threads/${thread.id}/messages`, {params: {from: thread.loaded_msgs_count}})
					.then(response => {
						this.messages_loading = false;
						let messages = response.data;
						thread.messages.splice(0, 0, ...messages);
						this.$forceUpdate();
						thread.loaded_msgs_count += messages.length;
						this.scroll_to_msgbox_id_on_update = `msg${last_loaded_msg_id}`;
					})
					.catch(error => {

					});
				}
			},
			incrementThreadMessageCount(thread) {
				thread.loaded_msgs_count += 1;
				thread.msgs_count += 1;
			},
			send() {
				this.$v.form.$touch()
				let isValid = !this.$v.form.$invalid
				let active_thread = this.activeThread;
				let send_options = {
					data: {
						users_id_to: [this.new_chat_user ? this.new_chat_user.id : null],
						subject: active_thread.subject,
						message: this.form.message,
					},
					method: !active_thread.id ? "POST" : "PUT",
					url: `${this.$root.default_api_prefix}/threads` + (active_thread.id ? `/${active_thread.id}/addmessage` : '')
				};
				if (this.form.attached_files) {
					send_options.data.attached_files = this.form.attached_files;
				}

				if(isValid) {
					axios(send_options)
					.then(response => {
						let thread_data = response.data;
						if (active_thread.id) {
							this.activeThread.messages.push(thread_data)

							this.scrollChat()

							this.incrementThreadMessageCount(this.activeThread)
							this.scroll_to_msgbox_id_on_update = this.is_show_attachment_dropzone ? 'dzattach' : 'msginput'
						} else {
							Vue.set(this.threads, this.active_thread_index, thread_data);
							Vue.set(this.activeThread, 'loaded_msgs_count', thread_data.messages.length);
							
							this.scrollChat()
						}

						this.form.message = null;
						delete this.form.attached_files;
						this.$refs.AttachDropzone.removeAllFiles();
					})
					.catch(error => {

					})
				} else {
					console.log('FILL FORM');
				}
			},
			setReadNewMessages() {
				let thread = this.activeThread;
				if (thread.new_msgs && thread.new_msgs.length) {
					axios.patch(`${this.$root.default_api_prefix}/messages/setread`, {messages: thread.new_msgs})
					.then(response => {
						for (let message of thread.messages) {
							if (thread.new_msgs.indexOf(message.id) >= 0) {
								let index = message.collocutors_not_read.indexOf(this.auth.id);
								if (index >= 0) {
									message.collocutors_not_read.splice(index, 1);
								}
							}
						}
						Vue.set(thread, 'new_msgs', []);
						Vue.set(thread, 'new_msgs_count', 0);
					})
					.catch(error => {

					});
				}
			},
			sortByType(item) {
				this.activeFilter = item
			},
			createNewChat() {
				this.threads.push({
					id: null,
					collocutors: [this.new_chat_user],
					subject: this.new_thread_subject,
				});

				this.active_thread_index = this.threads.length - 1;
			},
			handleQuryParams() {
				let query = this.$route.query;

				if (query.open) {
					let chat_index = this.threads.findIndex(x => x.id == query.open);
					if (chat_index >= 0) {
						this.openChat(chat_index, this.threads[chat_index]);
					}
				}
			},

			//LARAVEL ECHO HANDLING METHODS
			connToThread(thread) {
				return Echo.join(`thread.${thread.id}`);
			},
			subscribeToThreadChannel(thread) {
				console.log('SUBSR THREAD', thread);
				this.connToThread(thread)
				.listenForWhisper('typing', (e) => {
					Vue.set(thread, 'is_active', e);
					if (thread.typing_timer) clearTimeout(thread.typing_timer);

					thread.typing_timer = setTimeout(() => {
						Vue.set(thread, 'is_active', false);
					}, 2000);
					})
				.here((users) => {
					console.log('HERE', users);
					users.splice(users.indexOf(auth.id), 1);
					thread.active_collocutors.push(...users);
					this.$forceUpdate();
				})
				.joining((user) => {
					if (user != auth.id) {
						console.log('JOIN', user);
						thread.active_collocutors.push(user);
						this.$forceUpdate();
					}
				})
				.leaving((user) => {
					if (user != auth.id) {
						console.log('LEAVE', user);
						thread.active_collocutors.splice(thread.active_collocutors.indexOf(user), 1);
						this.$forceUpdate();
					}
				})
				.listen('.message.added', (e) => {
					console.log('MESSAGE ADDED', e);
					console.log(thread);
					let message = e.message_info;
					let msg_index = thread.messages.findIndex(x => x.id == message.id);
					if (msg_index < 0) {
						thread.messages.push(message);
						this.incrementThreadMessageCount(thread);
						thread.new_msgs.push(message.id);
						thread.new_msgs_count += 1;
					}
					this.setNewFirstNewMsgIndex(thread);
					this.scroll_to_msgbox_id_on_update = this.getMessageElementId(message);
					Vue.set(thread, 'is_active', false);
				})
				.listen('.message.read', (e) => {
					console.log('MESSAGE READ', e);
					console.log(thread);
					if (thread.messages) {
						for (let message of thread.messages) {
							if (e.messages.indexOf(message.id) >= 0) {
								Vue.set(message, 'is_read', true);
								this.$forceUpdate();
							}
						}
					}
				});
				console.log("THREAD SUBSCRIBE WEBSOCKETS", thread.id);
			},
			leaveThreadChannel(thread) {
				Echo.leave(`thread.${thread.id}`);
			},

			//SCROLLING EVENTS HANDLERS
			scrollToMsgBoxElement(theElement) {
				let element = null;
				if (typeof theElement === "string") {
					element = document.getElementById(theElement);
				}

				this.msgbox.scrollTo({
					left: element.offsetLeft,
					top: element.offsetTop,
					behavior: "instant"
				});
		 },

			//METHODS FROM VIEW PROPERTIES
			threadLastMsgShow(thread) {
				if (thread.is_active) return `${thread.is_active.name} is typing...`;
				return thread.last_message ? thread.last_message.short_text : '';
			},
			setNewFirstNewMsgIndex(thread) {
				if (
					thread.new_msgs &&
					thread.new_msgs.length &&
					((thread.new_first_msg_index === null) || (thread.new_first_msg_index === undefined))
					) {
					let first_id = thread.new_msgs[0];
				thread.new_first_msg_index = thread.messages.findIndex(x => x.id == first_id);
			}
		},
		getMessageElementId(message) {
			return `msg${message.id}`;
		},
		isNewDateInMessage(messages, index) {
			return (index == 0) || ((index > 0) && (messages[index].created_at_formatted_date != messages[index - 1].created_at_formatted_date));
		},
		isNewDateTimeInMessage(messages, index) {
			return this.isNewDateInMessage(messages, index) || 
			((index > 0) && ((messages[index].created_at_formatted_time != messages[index - 1].created_at_formatted_time) || (messages[index].user_id_from != messages[index - 1].user_id_from)));
		},
		isCollocutorsActive(thread) {
			return thread.active_collocutors && thread.active_collocutors.length;
		},

			//MESSAGE STYLE METHODS
			newMessageStyle(message) {
				let index = message.collocutors_not_read.indexOf(this.auth.id);
				return (index >= 0) && (message.user_id_from != this.auth.id) ? 'new-message' : '';
			},
			notReadMessageStyle(message) {
				return !message.is_read && (message.user_id_from == this.auth.id) ? 'not-read-message' : '';
			},
			messageSideStyle(message) {
				return (message.user_id_from == this.auth.id) ? 'align-items-end' : 'align-items-start';
			},
			messageStyle(message) {
				return [this.messageSideStyle(message), this.notReadMessageStyle(message), this.newMessageStyle(message)];
			},
			getAttachmentIconName(type) {
				switch (type) {
					case 'image':
					return 'fa fa-file-image-o';
					break;
					case 'video':
					return 'fa fa-file-video-o';
					break;
					default:
					return 'fa fa-file-o';
					break;
				}
			},
			isShowAttachmentPreview(type) {
				return (type == 'image') || (type == 'video');
			},

			//EVENT HANDLERS
			onMessageInput(thread) {
				this.setReadNewMessages();
				delete thread.new_first_msg_index;
			},
			onMsgBoxScroled() {
				if (this.msgbox.scrollTop === 0) {
			  	this.loadMoreThreadMessages();
			  }
			},
			onTyping() {
				this.connToThread(this.activeThread)
				.whisper('typing', {
					name: this.auth.name
				});
			},
		},

		//HOOKS
		created() {
			//
		},
		updated() {
			if (!this.msgbox) {
				this.msgbox = document.getElementById('msgbox');
				if (this.msgbox) {
					let handler = () => {
						this.onMsgBoxScroled();
					}
					msgbox.onscroll = handler;
					msgbox.onwheel = handler;
				}
			}

			if(this.scroll_to_msgbox_id_on_update) {
				this.scrollToMsgBoxElement(this.scroll_to_msgbox_id_on_update);
				this.scroll_to_msgbox_id_on_update = null;
			}
		},
		beforeRouteEnter(to, from, next) {
			next(vm => {
				let collocutors_promise = axios.get(`${vm.$root.default_api_prefix}/users/collocutors`)
				.then(response => {
					vm.users = response.data;
					resolve();
				})
				.catch(error => {

				});

				let threads_promise = axios.get(`${vm.$root.default_api_prefix}/threads`)
				.then(response => {
					let threads = response.data;
					Vue.set(vm, 'threads', threads);
					vm.handleQuryParams();

					for (let thread of threads) {
						thread.active_collocutors = [];
						vm.subscribeToThreadChannel(thread);
						vm.setNewFirstNewMsgIndex(thread);
					}

					resolve();
				})
				.catch(error => {

				});

				//set data loaded flag when all necessary datas loaded
				Promise.all([collocutors_promise, threads_promise]).then(values => {
					vm.data_loaded = true;
				});
			});
		},
		beforeRouteLeave(to, from, next) {
			this.data_loaded = false;
			//unsubscribe from channels on leaving
			for (let thread of this.threads) {
				this.leaveThreadChannel(thread);
			}
			this.threads = [];
			this.users = [];
			next();
		},

	}
</script>

<style lang="sass">
.new-message
	background-color: #e9e9e9
	.not-read-message
		background-color: #ffffbf
	</style>