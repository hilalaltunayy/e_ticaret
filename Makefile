GITNEXUS_COMPOSE=ai/gitnexus/runtime/compose.yaml
GITNEXUS_CONTAINER=gitnexus
GITNEXUS_VERSION=1.6.3
GITNEXUS_WORKDIR=/workspace

.PHONY: \
	gitnexus-up \
	gitnexus-down \
	gitnexus-status \
	gitnexus-list \
	gitnexus-analyze \
	gitnexus-mcp \
	gitnexus-smoke \
	gitnexus-volume-check

gitnexus-up:
	docker compose -f $(GITNEXUS_COMPOSE) up -d

gitnexus-down:
	docker compose -f $(GITNEXUS_COMPOSE) down

gitnexus-status:
	docker exec $(GITNEXUS_CONTAINER) sh -lc "cd $(GITNEXUS_WORKDIR) && npx -y gitnexus@$(GITNEXUS_VERSION) status"

gitnexus-list:
	docker exec $(GITNEXUS_CONTAINER) sh -lc "cd $(GITNEXUS_WORKDIR) && npx -y gitnexus@$(GITNEXUS_VERSION) list"

gitnexus-analyze:
	docker exec $(GITNEXUS_CONTAINER) sh -lc "cd $(GITNEXUS_WORKDIR) && npx -y gitnexus@$(GITNEXUS_VERSION) analyze --verbose"

gitnexus-mcp:
	docker exec -i $(GITNEXUS_CONTAINER) sh -lc "cd $(GITNEXUS_WORKDIR) && npx -y gitnexus@$(GITNEXUS_VERSION) mcp"

gitnexus-smoke:
	docker exec $(GITNEXUS_CONTAINER) node -v
	docker exec $(GITNEXUS_CONTAINER) npm -v
	docker exec $(GITNEXUS_CONTAINER) sh -lc "cd $(GITNEXUS_WORKDIR) && npx -y gitnexus@$(GITNEXUS_VERSION) --version"
	docker exec $(GITNEXUS_CONTAINER) sh -lc "cd $(GITNEXUS_WORKDIR) && npx -y gitnexus@$(GITNEXUS_VERSION) status"

gitnexus-volume-check:
	docker exec $(GITNEXUS_CONTAINER) sh -lc "mount | grep $(GITNEXUS_WORKDIR)/.gitnexus || true"
	docker exec $(GITNEXUS_CONTAINER) sh -lc "ls -lah $(GITNEXUS_WORKDIR)/.gitnexus | head"
