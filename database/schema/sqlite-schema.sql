CREATE TABLE "migrations"(
  "id" integer primary key autoincrement not null,
  "migration" varchar not null,
  "batch" integer not null
);
CREATE TABLE "users"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "email" varchar not null,
  "email_verified_at" datetime,
  "password" varchar not null,
  "remember_token" varchar,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "users_email_unique" on "users"("email");
CREATE TABLE "password_reset_tokens"(
  "email" varchar not null,
  "token" varchar not null,
  "created_at" datetime,
  primary key("email")
);
CREATE TABLE "sessions"(
  "id" varchar not null,
  "user_id" integer,
  "ip_address" varchar,
  "user_agent" text,
  "payload" text not null,
  "last_activity" integer not null,
  primary key("id")
);
CREATE INDEX "sessions_user_id_index" on "sessions"("user_id");
CREATE INDEX "sessions_last_activity_index" on "sessions"("last_activity");
CREATE TABLE "cache"(
  "key" varchar not null,
  "value" text not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE INDEX "cache_expiration_index" on "cache"("expiration");
CREATE TABLE "cache_locks"(
  "key" varchar not null,
  "owner" varchar not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE INDEX "cache_locks_expiration_index" on "cache_locks"("expiration");
CREATE TABLE "jobs"(
  "id" integer primary key autoincrement not null,
  "queue" varchar not null,
  "payload" text not null,
  "attempts" integer not null,
  "reserved_at" integer,
  "available_at" integer not null,
  "created_at" integer not null
);
CREATE INDEX "jobs_queue_index" on "jobs"("queue");
CREATE TABLE "job_batches"(
  "id" varchar not null,
  "name" varchar not null,
  "total_jobs" integer not null,
  "pending_jobs" integer not null,
  "failed_jobs" integer not null,
  "failed_job_ids" text not null,
  "options" text,
  "cancelled_at" integer,
  "created_at" integer not null,
  "finished_at" integer,
  primary key("id")
);
CREATE TABLE "failed_jobs"(
  "id" integer primary key autoincrement not null,
  "uuid" varchar not null,
  "connection" varchar not null,
  "queue" varchar not null,
  "payload" text not null,
  "exception" text not null,
  "failed_at" datetime not null default CURRENT_TIMESTAMP
);
CREATE INDEX "failed_jobs_connection_queue_failed_at_index" on "failed_jobs"(
  "connection",
  "queue",
  "failed_at"
);
CREATE UNIQUE INDEX "failed_jobs_uuid_unique" on "failed_jobs"("uuid");
CREATE TABLE "categories"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE "tags"(
  "id" integer primary key autoincrement not null,
  "created_at" datetime,
  "updated_at" datetime,
  "name" varchar
);
CREATE TABLE "product_tag"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "tag_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("product_id") references "products"("id") on delete cascade,
  foreign key("tag_id") references "tags"("id") on delete cascade
);
CREATE TABLE "units"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "short_name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE "suppliers"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "email" varchar not null,
  "phone" varchar not null,
  "address" varchar not null,
  "city" varchar not null,
  "is_active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "suppliers_email_unique" on "suppliers"("email");
CREATE TABLE "product_variants"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "price" numeric not null,
  "stock_qty" integer not null default '0',
  "cost" numeric not null,
  "is_active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  "name" varchar,
  foreign key("product_id") references "products"("id") on delete cascade
);
CREATE TABLE "attributes_values"(
  "id" integer primary key autoincrement not null,
  "attribute_id" integer not null,
  "value" varchar not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("attribute_id") references "attributes"("id") on delete cascade
);
CREATE TABLE "product_variant_values"(
  "id" integer primary key autoincrement not null,
  "product_variant_id" integer not null,
  "attribute_value_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("product_variant_id") references "product_variants"("id") on delete cascade,
  foreign key("attribute_value_id") references "attributes_values"("id")
);
CREATE TABLE "product_images"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "image_path" varchar not null,
  "sort_order" integer not null default '0',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("product_id") references "products"("id") on delete cascade
);
CREATE TABLE "products"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "status" varchar not null default('In Stock'),
  "category_id" integer,
  "is_active" tinyint(1) not null default('1'),
  "unit_id" integer,
  "product_code" varchar,
  "product_images_id" integer,
  foreign key("category_id") references categories("id") on delete no action on update no action,
  foreign key("product_images_id") references "product_images"("id") on delete set null
);
CREATE TABLE "general_settings"(
  "id" integer primary key autoincrement not null,
  "site_name" varchar,
  "site_logo" varchar,
  "site_favicon" varchar,
  "site_email" varchar,
  "site_phone" varchar,
  "site_address" varchar,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE "purchases"(
  "id" integer primary key autoincrement not null,
  "reference" varchar not null,
  "total_price" numeric not null default '0',
  "grand_total" numeric not null default '0',
  "purchase_item_id" integer,
  "supplier_id" integer not null,
  "payment_status" varchar check("payment_status" in('pending', 'paid', 'partial')) not null default 'pending',
  "shipping_cost" numeric not null default '0',
  "shipping_status" varchar not null default 'processing',
  "created_at" datetime,
  "updated_at" datetime,
  "purchase_date" date,
  "image_path" varchar,
  foreign key("purchase_item_id") references "purchase_items"("id") on delete cascade,
  foreign key("supplier_id") references "suppliers"("id") on delete cascade
);
CREATE UNIQUE INDEX "purchases_reference_unique" on "purchases"("reference");
CREATE TABLE "purchase_items"(
  "id" integer primary key autoincrement not null,
  "purchase_id" integer not null,
  "product_variant_id" integer not null,
  "unit_id" integer not null,
  "quantity" numeric not null default '0',
  "unit_price" numeric not null default '0',
  "sub_total" numeric not null default '0',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("purchase_id") references "purchases"("id") on delete cascade,
  foreign key("product_variant_id") references "product_variants"("id") on delete cascade,
  foreign key("unit_id") references "units"("id") on delete cascade
);
CREATE TABLE "permissions"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "guard_name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "permissions_name_guard_name_unique" on "permissions"(
  "name",
  "guard_name"
);
CREATE TABLE "roles"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "guard_name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "roles_name_guard_name_unique" on "roles"(
  "name",
  "guard_name"
);
CREATE TABLE "model_has_permissions"(
  "permission_id" integer not null,
  "model_type" varchar not null,
  "model_id" integer not null,
  foreign key("permission_id") references "permissions"("id") on delete cascade,
  primary key("permission_id", "model_id", "model_type")
);
CREATE INDEX "model_has_permissions_model_id_model_type_index" on "model_has_permissions"(
  "model_id",
  "model_type"
);
CREATE TABLE "model_has_roles"(
  "role_id" integer not null,
  "model_type" varchar not null,
  "model_id" integer not null,
  foreign key("role_id") references "roles"("id") on delete cascade,
  primary key("role_id", "model_id", "model_type")
);
CREATE INDEX "model_has_roles_model_id_model_type_index" on "model_has_roles"(
  "model_id",
  "model_type"
);
CREATE TABLE "role_has_permissions"(
  "permission_id" integer not null,
  "role_id" integer not null,
  foreign key("permission_id") references "permissions"("id") on delete cascade,
  foreign key("role_id") references "roles"("id") on delete cascade,
  primary key("permission_id", "role_id")
);

INSERT INTO migrations VALUES(1,'0001_01_01_000000_create_users_table',1);
INSERT INTO migrations VALUES(2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO migrations VALUES(3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO migrations VALUES(4,'2026_08_20_034201_create_products_table',1);
INSERT INTO migrations VALUES(5,'2026_08_20_072935_add_status_to_products_table',1);
INSERT INTO migrations VALUES(6,'2026_08_20_073355_create_categories_table',1);
INSERT INTO migrations VALUES(7,'2026_08_20_073447_add_category_id_to_products_table',1);
INSERT INTO migrations VALUES(8,'2026_08_20_074801_create_tags_table',1);
INSERT INTO migrations VALUES(9,'2026_08_20_074902_create_product_tag_table',1);
INSERT INTO migrations VALUES(10,'2026_08_20_075128_add_name_to_tags_table',1);
INSERT INTO migrations VALUES(11,'2026_08_20_082020_add_is_active_to_products_table',1);
INSERT INTO migrations VALUES(13,'2026_08_21_035552_create_units_table',1);
INSERT INTO migrations VALUES(14,'2026_08_21_035703_create_suppliers_table',1);
INSERT INTO migrations VALUES(15,'2026_08_21_040808_create_product_variants_table',1);
INSERT INTO migrations VALUES(16,'2026_08_21_041835_remove_name_from_product_variants_table',1);
INSERT INTO migrations VALUES(17,'2026_08_21_051615_create_attributes_values_table',1);
INSERT INTO migrations VALUES(18,'2026_08_21_051730_create_product_variant_values_table',1);
INSERT INTO migrations VALUES(19,'2026_08_21_052058_add_name_to_product_variants_table',1);
INSERT INTO migrations VALUES(20,'2026_08_21_072216_add_unit_to_products_table',1);
INSERT INTO migrations VALUES(21,'2026_08_24_034637_remove_cost_and_price_from_products_table',1);
INSERT INTO migrations VALUES(22,'2026_08_24_040228_rename_sku_from_product_variants_table',1);
INSERT INTO migrations VALUES(23,'2026_08_24_044021_add_product_code_to_products_table',1);
INSERT INTO migrations VALUES(24,'2026_08_24_044053_remove_product_code_from_product_variants_table',1);
INSERT INTO migrations VALUES(25,'2026_08_24_073625_create_product_images_table',1);
INSERT INTO migrations VALUES(26,'2026_08_24_074330_add_product_images_id_to_products_table',1);
INSERT INTO migrations VALUES(27,'2026_08_24_092736_create_general_settings_table',1);
INSERT INTO migrations VALUES(28,'2026_08_25_062420_create_purchases_table',1);
INSERT INTO migrations VALUES(29,'2026_08_25_064259_remove_column_from_purchases',1);
INSERT INTO migrations VALUES(30,'2026_08_25_064416_add_purchase_date_to_purchases_table',1);
INSERT INTO migrations VALUES(31,'2026_08_25_092818_create_purchase_items_table',1);
INSERT INTO migrations VALUES(32,'2026_08_20_083806_create_permission_tables',2);
INSERT INTO migrations VALUES(33,'2026_08_25_093607_add_image_path_to_purchases_table',3);
