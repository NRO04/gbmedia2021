<template>
	<li class="c-header-nav-item px-3 c-d-legacy-none">
		<div class="c-avatars-stack mt-2">
			<div class="c-avatar c-avatar-xs" v-for='notification in notifications' :title="notification.data.username">
				<img class="c-avatar-img shadowAnimation" :src="'/assets/img/avatars/' + notification.data.avatar">
			</div>
		</div>
	</li>
</template>
<style scoped>
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
import moment from "moment";
import"moment/locale/es";
	export default{
		name: "chatnotification",
		props: ['user'],
		data(){
			return {
				notifications: [],
				moment : moment,
			}
		},
		created(){
			this.getNotifications();

			Echo.private(`App.User.${this.user}`)
				.notification(notification => {
					console.log(notification);
					this.notification = notification.data;
					if (notification.dataType == "ChatNotification")
					{
						this.notifications.push(notification);
					}
			});

			Echo.private(`App.User.${this.user}`)
				.notification(notification => {
					console.log(notification);
					if (notification.data.dataType == "ChatMessageReceived")
					{
						alert("mensaje recibido");
						this.getNotifications();
					}
			});
		},
		methods: {
			getNotifications(){
				axios.get(route('chat.notifications')).then(response => {
					this.notifications = response.data;
				});
			}
		}
	}
</script>
