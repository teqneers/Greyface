ALTER TABLE `tq_alias`
ADD CONSTRAINT `constraint_tq_alias___tq_user`
FOREIGN KEY (`user_id`)
REFERENCES `tq_user` (`user_id`)
ON DELETE CASCADE