<template>
	<li class="c-header-nav-item dropdown d-md-down-none mx-2"><a class="c-header-nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
        <i class="fas fa-envelope-open"></i>
        <span class="badge badge-pill badge-info">{{ notifications.length }}</span></a>
    <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg pt-0" style="max-width: 400px">
		<div class="dropdown-header bg-light"><strong>You have {{ notifications.length }} messages</strong></div>
		<a class="dropdown-item" :href="'/tasks/list'" v-for="notification in notifications.slice(0,5)">
			<div class="message" style="min-width: 360px">
				<div class="py-3 mfe-3 float-left">
					<div class="c-avatar"><img class="c-avatar-img" :src="'/assets/img/avatars/' + notification.data.avatar">
						<span class="c-avatar-status bg-success"></span>
					</div>
				</div>
				<div>
					<small class="text-muted">{{ notification.data.username }}</small>
					<small class="text-muted float-right mt-1">{{ moment(notification.created_at).fromNow() }}</small>
				</div>
				<div class="font-weight-bold text-truncate">
					 {{ notification.data.title }}
				</div>
				<div class="small text-muted text-truncate">{{ notification.data.comment }}</div>
			</div>
		</a>
		<a class="dropdown-item text-center border-top" :href="'/tasks/list'"><strong>View all messages</strong></a>
    </div>
  </li>
</template>

<script>
import moment from "moment";
import"moment/locale/es";

export default{
	name: "notification",
	props: ['user'],
	data(){
		return {
			notifications: [],
			moment : moment,
		}
	},
	methods: {
		getNotifications(){
			axios.get('/tasks/notifications')
			.then(response => {
				this.notifications = response.data;
				Echo.private(`App.User.${this.user}`)
					.notification(notification => {
						if (notification.dataType == "TaskCommentNotification")
						{
							this.notifications.unshift(notification);
							Toast.fire({
						        icon: "info",
						        title: "Ha recibido una nueva notificacion...",
						    });
						}

					})
			});
		}
	},
	mounted(){
		this.getNotifications();
		Echo.private(`App.User.${this.user}`).listen('NewMessage' , (e) => {
			axios.get('/tasks/notifications')
			.then(response => {
				this.notifications = response.data;
			});
		})
	}
}
</script>

<style scoped>
	.dropdown-item{
		padding: 0.2rem 1rem;
	}
</style>
