<template>
<div class="row">

	<!--messages-->
	<div class="col-md-8 chat">
		<div class="card card-default">
			<div class="card-header">
				<div class="d-flex bd-highlight">
					<div v-if="talking_with.avatar" class="c-avatar c-avatar-xs">
						<img class="c-avatar-img" :src="'/assets/img/avatars/' + talking_with.avatar">
					</div>
					<div class="ml-2 mt-1">
						<h5><span>{{ talking_with.full_name }}</span></h5>
					</div>
				</div>
				<span id="action_menu_btn" v-show="messages.length > 0" ><i class="fas fa-ellipsis-v"></i></span>
				<div class="action_menu">
					<ul>
						<li><i class="fas fa-user-circle"></i> View profile</li>
						<li><i class="fas fa-users"></i> Add to close friends</li>
						<li><i class="fas fa-plus"></i> Add to group</li>
						<li><i class="fas fa-ban"></i> Block</li>
					</ul>
				</div>
			</div>
			<div class="card-body msg_card_body" v-chat-scroll>
				<div class="row">
					<div class="mb-4 col-md-12" 
					v-for='(message, index) in messages' 
					:key="index"
					:class="message.user_id == user.id? 'justify-content-end' : 'justify-content-start'">
					<!--Message sent-->
						<div class="row" v-if="message.user_id == user.id">
							<div class="col-lg-12">
								<div class="row">
									<div class="col-lg-11" style="padding-right: inherit;">
										<div class="msg_cotainer_send" style="float:right" >
											{{ message.message }}
											<span :class="message.status == 0? 'text-muted': 'text-info'" style="float-right"><i style="font-size: 10px" class="fa fa-check"></i><i class="fa fa-check" style="font-size: 10px;margin-left: -5px;"></i></span>
										</div>
										<div style="float-right">
											<span class="msg_time_send">{{ message.created_at }}</span>
										</div>
									</div>
										
									<div class="img_cont_msg col-lg-1" style="padding-left: 5px">
										<div class="c-avatar c-avatar-xs">
						                	<img class="c-avatar-img" :src="'/assets/img/avatars/'+message.avatar" :alt="message.full_name">
						                </div>
									</div>
								</div>
								
							</div>	
						</div>
					<!--/Message sent-->
					
					<!--Message received-->
						<div class="row" v-else>
							<div class="col-lg-12">
								<div class="row">
									<div class="img_cont_msg col-lg-1">
										<div class="c-avatar c-avatar-xs">
						                	<img class="c-avatar-img" :src="'/assets/img/avatars/'+message.avatar" :alt="message.full_name">
						                </div>
									</div>
									<div class="col-lg-11" style="padding-left: inherit;">
										<div class="msg_cotainer" style="float:left" >
											{{ message.message }}
											
										</div>
										<div style="float-left">
											<span class="msg_time">{{ message.created_at }}</span>
										</div>
									</div>
								</div>
								
							</div>	
						</div>
					<!--/Message received-->
					</div>
				</div>
				

				<!--Icon when messages is empty-->
				<div v-show="messages.length == 0" class="container h-100 chat_icon">
					<div class="row h-100 justify-content-center align-items-center text-center">
						<div class="col-md-12">
							<img src='/images/svg/chat.svg'>
						</div>
					</div>
				</div>
				
			</div>
			<div class="card-footer">
				<div class="input-group">
					<div class="input-group-append">
						<span class="input-group-text btn_emoji"><i class="far fa-laugh"></i></span>
					</div>
					<textarea
						@keydown = "sendTypingEvent" 
						@keyup.enter = "sendMessage" 
						@click="changeStatusMessage"
						v-model="newMessage"
						type="text" 
						class="form-control type_msg" 
						name="message" 
					></textarea>
					<div class="input-group-append">
						<span class="input-group-text btn_attach"><i class="fas fa-paperclip"></i></span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!--/messages-->

	<div class="col-lg-4">
		<div class="list-group list-group-accent">
            <div class="list-group-item list-group-item-accent-secondary bg-light text-center font-weight-bold text-muted text-uppercase c-small">Activos
            </div>
            <div class="c-avatars-stack m-3" style="min-height: 35px; cursor: pointer;">
                <div class="c-avatar c-avatar-xs" v-for="(user , index) in friendsActives" :key="index">
                	<img class="c-avatar-img shadowAnimation" :src="'/assets/img/avatars/'+user.avatar" :title="user.first_name+' '+user.last_name" @click="chatInCommon(user.id)">
                </div>
	        </div>
		</div>	
		<div class="nav-tabs-boxed">
	        <ul class="nav nav-tabs" role="tablist">
	          <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-contacts-1" role="tab" aria-controls="tab-contacts">Contactos</a></li>
	          <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-options-1" role="tab" aria-controls="tab-options"><i class="fas fa-cogs text-success"></i></a></li>
	        </ul>
	        <div class="tab-content pl-0 pr-0">
	          <div class="tab-pane active" id="tab-contacts-1" role="tabpanel">
	          	<div class="container">
	    			<div class="row" 
	    				v-for="(friend, index) in friends" 
				        :id="'chat-' + friend.chat"
				        @click = "activeChat(friend.chat)" 
				        style="cursor: pointer" >
        				<div class="col-lg-2">
        					<div class="c-avatar">
								<img class="c-avatar-img" :src="'/assets/img/avatars/' + friend.avatar"  alt="user@email.com">
							</div>
        				</div>
        				<div class="col-lg-7 pt-2"><span class="text-muted">{{ friend.full_name}}</span></div>
        				<div class="col-lg-1 pt-2">
        					<div class="sk-wave" style="margin: auto; font-size: 16; height: 15px; display: none" v-show="friend.whispering > 0" :ref="'whispering_friend_'+friend.chat">
								<div class="sk-rect sk-rect1" style="width: 2px"></div>
								<div class="sk-rect sk-rect2" style="width: 2px"></div>
								<div class="sk-rect sk-rect3" style="width: 2px"></div>
								<div class="sk-rect sk-rect4" style="width: 2px"></div>
							</div>
        				</div>
        			</div>
        		</div>
	          </div>
	          <div class="tab-pane action_contacts" id="tab-options-1" role="tabpanel">
	          	<ul>
					<li data-toggle="modal" data-target="#modal-contact" id="create-contact"><i class="fas fa-user-plus"></i>Añadir Contacto</li>
					<li><i class="fas fa-user-plus"></i>Crear Grupo</li>
				</ul>
	          </div>
	       
	        </div>
	      </div>
	</div>

	<!--Add Contact-->
	<div id="modal-contact" class="modal fade" role="dialog">
		<div class="modal-dialog modal-lg modal-dark">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Buscar Contacto</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12">
							<input type="" class="form-control" name="" v-model="search_contact" placeholder="Buscar Contacto" @keyup="searchContact()">
						</div>
						<div class="col-lg-12" id="modal-body-contact" v-if="show_contacts.length > 0">
							<table class="table table-hover table-striped">
								<thead>
									<tr>
										<th>Usuario</th>
										<th>Rol</th>
										<th></th>
									</tr>
								</thead>
								<tbody>

									<tr v-for="contact in show_contacts" :key="contact.id" :class="contact.is_friend? 'bg-dark' : '' ">
										<td>
											<div class="c-avatar float-left">
												<img class="c-avatar-img" :src="'/assets/img/avatars/' + contact.avatar"  alt="user@email.com">
											</div>
											<div class="p-1 mt-1"> 
												<span class="ml-3 pt-2" :class="contact.is_friend? 'text-muted' : ''">
												{{ contact.first_name +" "+contact.last_name+" "+contact.second_last_name }}
												</span>
											</div>
										</td>
										<td :class="contact.is_friend? 'text-muted' : ''">{{ contact.role }}</td>
										<td>
											<button v-if="contact.is_friend" type="" class="btn btn-sm txt-primary" @click="chatInCommon(contact.user_id)" title="Hablar"><svg class="c-icon"><use xlink:href="http://gblaravel.test/vendors/@coreui/icons/svg/free.svg#cil-speech"></use></svg></button>

											<button v-else type="" class="btn btn-sm" @click="addContact(contact.user_id)" title="Agregar"><i class="fa fa-plus text-success"></i></button>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div v-else class="col-md-12">
							<div  class="alert alert-success text-center mt-2" role="alert">Puede buscar el usuario que desea agregar a sus contactos: por nombre, apellidos o por el rol</div>
						</div>
						
						
					</div>
					
				</div>
			</div>
		</div>
	</div>
	<!--/modal-->
</div>	
</template>

<style scoped>
	.chat{
		margin-top: auto;
		margin-bottom: auto;
	}
	.card{
		height: 500px;
		border-radius: 15px !important;
		background-color: rgba(0,0,0,0.4) !important;
	}
	.msg_card_body{
		overflow-y: auto;
	}
	.card-header{
		border-radius: 15px 15px 0 0 !important;
		border-bottom: 0 !important;
		padding: 0.3rem 1rem !important;
	}
	.card-footer{
		border-radius: 0 0 15px 15px !important;
		border-top: 0 !important;
	}

	.type_msg{
		background-color: #000 !important;
		border:0 !important;
		overflow-y: auto;
	}
	.type_msg:focus{
		box-shadow:none !important;
		outline:0px !important;
		background-color: #06070e !important;
	}
	.btn_emoji{
		border-radius: 15px 0 0 15px !important;
		background-color: rgba(0,0,0,0.3) !important;
		border:0 !important;
		color: white !important;
		cursor: pointer;
		background: #0a0b18 !important;
	}
	.btn_attach{
		border-radius: 0 15px 15px 0 !important;
		background-color: rgba(0,0,0,0.3) !important;
		border:0 !important;
		color: white !important;
		cursor: pointer;
		background: #0a0b18 !important;
	}
	.msg_cotainer{
		border-radius: 18px;
		background-color: #82ccdd;
		padding: 7px 10px;
		color: #000000;
	}
	.msg_cotainer_send{
		border-radius: 18px;
		background-color: #78e08f;
		padding: 7px 10px;
		color: #000000;
	}
	.msg_time{
		position: absolute;
		left: 0;
		bottom: -17px;
		color: rgba(255,255,255,0.5);
		font-size: 10px;
	}
	.msg_time_send{
		position: absolute;
		right:0;
		bottom: -17px;
		color: rgba(255,255,255,0.5);
		font-size: 10px;
	}
	#action_menu_btn{
		position: absolute;
		right: 10px;
		top: 10px;
		color: white;
		cursor: pointer;
		font-size: 20px;
	}
	.action_menu{
		z-index: 1;
		position: absolute;
		padding: 15px 0;
		background-color: rgba(0,0,0,0.5);
		color: white;
		border-radius: 15px;
		top: 30px;
		right: 15px;
		display: none;
	}
	.action_menu ul{
		list-style: none;
		padding: 0;
		margin: 0;
	}
	.action_menu ul li{
		width: 100%;
		padding: 10px 15px;
		margin-bottom: 5px;
	}
	.action_menu ul li i{
		padding-right: 10px;

	}
	.action_menu ul li:hover{
		cursor: pointer;
		background-color: rgba(0,0,0,0.2);
	}

	
	.action_contacts ul{
		list-style: none;
		padding: 0;
		margin: 0;
	}
	.action_contacts ul li{
		width: 100%;
		padding: 10px 15px;
		margin-bottom: 5px;
		text-align: left;
	}
	.action_contacts ul li i{
		padding-right: 10px;

	}
	.action_contacts ul li:hover{
		cursor: pointer;
		color: #45a164 !important;
	}

	.tab-content{
		background-color: #0a0b18 !important;
    	border-color: #23242d !important;
	}

	@media(max-width: 576px){
		.contacts_card{
			margin-bottom: 15px !important;
		}
	}
	.chat_icon img{
		height: 120px;
		opacity: .6;
	}

	#contacts-pane{
		max-height: 350px;
    	overflow-y: auto;
	}

	.shadowAnimation {
		box-shadow: 0px 0px 0px;
		animation: shadowAnimation;
		animation-duration: 3s;
		animation-iteration-count: 2; 
	}

	@keyframes shadowAnimation {
	  	0%{
			-webkit-box-shadow: 0px 0px 17px -1px rgba(19,214,71,1);
			-moz-box-shadow: 0px 0px 17px -1px rgba(19,214,71,1);
			box-shadow: 0px 0px 17px -1px rgba(19,214,71,1);
		}
		25%{
			-webkit-box-shadow: 0px 0px 17px 1px rgba(19,214,71,1);
			-moz-box-shadow: 0px 0px 17px 1px rgba(19,214,71,1);
			box-shadow: 0px 0px 17px 1px rgba(19,214,71,1);
		}
		50%{
			-webkit-box-shadow: 0px 0px 17px 4px rgba(19,214,71,1);
			-moz-box-shadow: 0px 0px 17px 4px rgba(19,214,71,1);
			box-shadow: 0px 0px 17px 4px rgba(19,214,71,1);
		}
		75%{
			-webkit-box-shadow: 0px 0px 17px 1px rgba(19,214,71,1);
			-moz-box-shadow: 0px 0px 17px 1px rgba(19,214,71,1);
			box-shadow: 0px 0px 17px 1px rgba(19,214,71,1);
		}
		100%{
			-webkit-box-shadow: 0px 0px 17px -2px rgba(19,214,71,1);
			-moz-box-shadow: 0px 0px 17px -2px rgba(19,214,71,1);
			box-shadow: 0px 0px 17px -2px rgba(19,214,71,1);
		}
	}
</style>

<script>
	export default{
		name: "chats",
		props: ['user'],
		data(){
			return{
				messages: [],
				newMessage: '',
				users: [],
				whisperingFriends: [],
				typingTimer: false,
				friends: [],
				friendsActives: [],
				contFriendsActives: 0,
				chat: 0,
				talking_with: [],
				search_contact: "",
				show_contacts : [],
			}
		},
		created(){
			this.getFriends();
		},
		mounted(){

			Echo.join('chat')
			.here(users => {
				this.users = users;
				for (var i = 0; i < this.users.length; i++) {
					let user_id = this.users[i].id;
					let cont = i;
					if (user_id != this.user.id) 
					{
						let response = this.Activefriends(user_id, cont);
					}
					
				}
			})
			.joining(user => {
				this.users.push(user);
				let user_id = user.id;
				axios.get('/chat/chatInCommon?user_id='+user_id).then(response => {
					if (response.data.exists) 
					{
						this.friendsActives.push(user);
					}
				})
			})
			.leaving(user => {
				this.users = this.users.filter(u => u.id != user.id);
				this.friendsActives = this.friendsActives.filter(u => u.id != user.id);
			})
			.listenForWhisper('typing', (e) => {
				if (e.chat_id != 0) 
				{
					for (var i = 0; i < this.friends.length; i++) {
						if (this.friends[i].chat == e.chat_id) 
						{
							this.friends[i].whispering = 2;
						}
					}
				}
				
			});

			setInterval(() => {
				for (var i = 0; i < this.friends.length; i++) {
					if (this.friends[i].whispering > 0) 
					{
						this.friends[i].whispering = this.friends[i].whispering - 1;
					}
				}
			}, 2000);

			Echo.private(`App.User.${this.user.id}`)
				.notification(response => {
					if (response.dataType == "ChatMessageNotification") 
					{
						if (response.data.chat_id == this.chat) 
						{
							this.messages.push(response.data);
						}
					}
					
				});

			Echo.private(`App.User.${this.user}`)
				.notification(notification => {
					console.log(notification);
					if (notification.data.dataType == "ChatMessageReceived") 
					{
						console.log("llega recibido");
					}
			});
		},
		methods: {
			activeChat(id){
				this.chat = id;
				this.fetchMessage();
			},
			async Activefriends(user_id, cont){
				let response = await axios.get('/chat/chatInCommon?user_id='+user_id)
				.then(response => {
					if (response.data.exists) 
					{
						this.friendsActives.push(this.users[cont]);
					}
					
				});
				console.log("async");
			},
			chatInCommon(user_id){
				$("#modal-contact").modal("hide");
				axios.get('/chat/chatInCommon?user_id='+user_id).then(response => {
					if (response.data.exists) 
					{
						this.activeChat(response.data.chat_id);
					}
				})
			},
			fetchMessage(){
				axios.get("/chat/fetchMessage?chat=" + this.chat).then(response => {
					this.messages = response.data.messages;
					this.talking_with = response.data.talking_with;
				})
			},
			sendMessage(){
				if (this.chat == 0) 
				{
					Toast.fire({
					        icon: "info",
					        title: "Debes seleccionar la persona que recibirá el mensaje",
					    });
				}
				else
				{
					axios.post('/chat/sendMessage', {
						chat : this.chat,
						message : this.newMessage
					}).then(response => {
						this.messages.push({
							full_name: this.user.first_name+" "+this.user.last_name,
							message: this.newMessage,
							avatar: this.user.avatar,
							user_id: this.user.id,
							status: 0,
						})
						this.newMessage = '';
					})
					.catch(error => {
						Toast.fire({
					        icon: "error",
					        title: "Upsss... Ha ocurrido un error, comuniquese con el ADMIN",
					    });
					});
				}
			},
			changeStatusMessage(){
				if (this.chat > 0) 
				{
					axios.get("/chat/changeStatusMessage?chat=" + this.chat).then(response => {})	
				}
			},
			sendTypingEvent(){
				Echo.join('chat')
					.whisper('typing', { user: this.user, chat_id : this.chat });
			},
			getFriends(){
				axios.get('/chat/getFriends').then(response => {
					this.friends = response.data;
					console.log(response);
				})
			},
			searchContact(){
				let search_contact = this.search_contact;
				search_contact = search_contact.trim();
				if (search_contact == "") 
				{
					this.show_contacts = [];
				}
				else
				{
					axios.get('/chat/searchContact?search_contact='+search_contact).then(response => {
						this.show_contacts = response.data;
					})	
				}
			},
			addContact(user_id){
				axios.post('/chat/addContact', {
					id : user_id,
				}).then(response => {
					Toast.fire({
				        icon: "success",
				        title: "Contacto agregado",
				    });
					this.searchContact();
					this.getFriends();
				}).catch((response) => {
					Toast.fire({
				        icon: "error",
				        title: "Ha ocurrido un error, contactese con el ADMIN",
				    });
				});	
			}

		}
	}
</script>