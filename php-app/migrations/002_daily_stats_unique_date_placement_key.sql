ALTER TABLE daily_stats
ADD CONSTRAINT uq_daily_stats_date_placement UNIQUE (stat_date, placement_id);