-- Reset all data (optional for development)
TRUNCATE TABLE post_tags, images, posts, tags, users RESTART IDENTITY CASCADE;

-- Insert tags
INSERT INTO tags (name, slug) VALUES
('総合', 'general'),
('テクノロジー', 'technology'),
('モバイル', 'mobile'),
('アプリ', 'apps'),
('エンタメ', 'entertainment'),
('ビューティー', 'beauty'),
('ファッション', 'fashion'),
('ライフスタイル', 'lifestyle'),
('ビジネス', 'business'),
('グルメ', 'gourmet'),
('スポーツ', 'sports');

-- Insert users
INSERT INTO users (user_id, user_name, email, password)
VALUES
  ('00000000-0000-0000-0000-000000000001', 'alice', 'alice@example.com', '$2y$12$EKrlg9LXQeHQdTD1J3pJSeCwOKrXdpKTFipv7tSSfLj9hGWy71uCC'),
  ('00000000-0000-0000-0000-000000000002', 'bob', 'bob@example.com', '$2y$12$ppNOxgEU1WMqtBB2gcp7euTcJfyaPiZohuqXzllYLwFMwd.1jRRXO');

-- Insert posts
INSERT INTO posts (post_id, user_id, title, slug, text, created_at)
VALUES
  ('10000000-0000-0000-0000-000000000001', '00000000-0000-0000-0000-000000000001', 'Tech Trends 2025', 'tech-trends-2025', 'This post discusses tech trends in 2025...', NOW()),
  ('10000000-0000-0000-0000-000000000002', '00000000-0000-0000-0000-000000000002', 'The Best Gourmet Spots', 'best-gourmet-spots', 'Explore gourmet food around Tokyo...', NOW());

-- Insert images (thumbnails and inline)
INSERT INTO images (image_id, post_id, image_path, alt_text)
VALUES
  -- Thumbnail for post 1
  ('20000000-0000-0000-0000-000000000001', '10000000-0000-0000-0000-000000000001', '/images/tech-thumb.jpg', 'Tech Thumbnail'),
  -- Thumbnail for post 2
  ('20000000-0000-0000-0000-000000000002', '10000000-0000-0000-0000-000000000002', '/images/gourmet-thumb.jpg', 'Gourmet Thumbnail'),
  -- Inline image for post 1
  ('20000000-0000-0000-0000-000000000003', '10000000-0000-0000-0000-000000000001', '/images/tech-graph.jpg', 'Tech Growth Chart');

-- Link thumbnails to posts
UPDATE posts SET thumbnail_image_id = '20000000-0000-0000-0000-000000000001' WHERE post_id = '10000000-0000-0000-0000-000000000001';
UPDATE posts SET thumbnail_image_id = '20000000-0000-0000-0000-000000000002' WHERE post_id = '10000000-0000-0000-0000-000000000002';

-- Insert post_tags
INSERT INTO post_tags (post_id, tag_id)
VALUES
  ('10000000-0000-0000-0000-000000000001', 2), -- テクノロジー
  ('10000000-0000-0000-0000-000000000001', 4), -- アプリ
  ('10000000-0000-0000-0000-000000000002', 10); -- グルメ
