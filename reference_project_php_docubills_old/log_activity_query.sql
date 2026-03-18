
                    SELECT DATE(created_at) AS label,
                           SUM(CASE WHEN status = 'Paid' THEN 1 ELSE 0 END) AS paid,
                           SUM(CASE WHEN status = 'Unpaid' THEN 1 ELSE 0 END) AS unpaid
                    FROM invoices
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                      AND deleted_at IS NULL
                    GROUP BY DATE(created_at)
                    ORDER BY label
            