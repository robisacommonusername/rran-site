CREATE TABLE IF NOT EXISTS users (
  id serial,
  username varchar(100) NOT NULL default '',
  password varchar(60) NOT NULL default '',
  real_name varchar(255),
  email varchar(255),
  phone varchar(31),
  is_admin boolean,
  created timestamp,
  modified timestamp,
  PRIMARY KEY (id)
);
CREATE UNIQUE INDEX on users(username);

CREATE TABLE IF NOT EXISTS inventoryitems (
  id serial,
  user_id integer,
  description text,
  created timestamp,
  modified timestamp,
  PRIMARY KEY (id)
);
CREATE INDEX on inventoryitems(user_id);

CREATE TABLE IF NOT EXISTS uploadedfiles (
  id serial,
  file_name varchar(255) NOT NULL,
  mime_type varchar(255),
  file_size integer,
  private boolean default false,
  content_key varchar(32),
  created timestamp,
  modified timestamp,
  PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS minutes (
  id serial,
  meeting_date date,
  file_name varchar(255) NOT NULL,
  mime_type varchar(255),
  file_size integer,
  content_key varchar(32),
  content text,
  created timestamp,
  modified timestamp,
  PRIMARY KEY (id)
);
CREATE UNIQUE INDEX on minutes(meeting_date);

CREATE TABLE IF NOT EXISTS tags (
  id serial,
  label varchar(255) NOT NULL default '',
  description text,
  PRIMARY KEY (id)
);
CREATE UNIQUE INDEX on tags(label);

CREATE TABLE IF NOT EXISTS uploadedfiles_tags (
  uploadedfile_id integer NOT NULL,
  tag_id integer NOT NULL,
  PRIMARY KEY (uploadedfile_id, tag_id)
);
CREATE INDEX on uploadedfiles_tags(uploadedfile_id);
CREATE INDEX on uploadedfiles_tags(tag_id);
