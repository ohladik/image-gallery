create schema gallery_app;

create table gallery_app.categories
(
	id int auto_increment
		primary key,
	name varchar(255) not null
)
;

create table gallery_app.posts
(
	id int auto_increment
		primary key,
	authorId varchar(255) not null,
	description varchar(255) null,
	category varchar(255) not null,
	imageFile varchar(255) not null,
	createdAt datetime default CURRENT_TIMESTAMP not null
)
;

create table gallery_app.users
(
	id int auto_increment
		primary key,
	email varchar(255) not null,
	password varchar(255) not null,
	role varchar(255) not null
)
;

create table gallery_app.likes
(
	user_id int not null,
	post_id int not null
)
;
