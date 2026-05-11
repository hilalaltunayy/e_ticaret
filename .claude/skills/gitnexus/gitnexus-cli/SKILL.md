---
name: gitnexus-cli
description: "Use when the user needs to run GitNexus CLI commands like analyze/index a repo, check status, clean the index, generate a wiki, or list indexed repos. Examples: \"Index this repo\", \"Reanalyze the codebase\", \"Generate a wiki\""
---

# GitNexus CLI Commands

All commands run inside the `gitnexus` Docker container.

## Commands

### analyze - Build or refresh the index

```bash
docker exec gitnexus sh -lc "cd /workspace && npx -y gitnexus@1.6.3 analyze --verbose"
```

Run from the container against `/workspace`. This parses source files, builds the knowledge graph, and writes it to `/workspace/.gitnexus`.

| Flag | Effect |
| --- | --- |
| `--force` | Force full re-index even if up to date |
| `--embeddings` | Enable embedding generation for semantic search (off by default) |
| `--drop-embeddings` | Drop existing embeddings on rebuild |

**When to run:** First time in a project, after major code changes, or when `gitnexus://repo/{name}/context` reports the index is stale.

### status - Check index freshness

```bash
docker exec gitnexus sh -lc "cd /workspace && npx -y gitnexus@1.6.3 status"
```

Shows whether the current repo has a GitNexus index, when it was last updated, and symbol/relationship counts.

### list - Show all indexed repos

```bash
docker exec gitnexus sh -lc "cd /workspace && npx -y gitnexus@1.6.3 list"
```

Lists indexed repositories known to the containerized GitNexus runtime.

### mcp - Start MCP server

```bash
docker exec -i gitnexus sh -lc "cd /workspace && npx -y gitnexus@1.6.3 mcp"
```

Starts GitNexus MCP from inside the container.

### clean - Delete the index

```bash
docker exec gitnexus sh -lc "cd /workspace && npx -y gitnexus@1.6.3 clean"
```

Deletes `/workspace/.gitnexus` from the container filesystem/volume.

### wiki - Generate documentation from the graph

```bash
docker exec gitnexus sh -lc "cd /workspace && npx -y gitnexus@1.6.3 wiki"
```

Generates repository documentation from the knowledge graph using an LLM.

## After Indexing

1. Read `gitnexus://repo/{name}/context` to verify the index loaded.
2. Use the other GitNexus skills (`exploring`, `debugging`, `impact-analysis`, `refactoring`) for your task.

## Troubleshooting

- If index is stale, run the Docker-based GitNexus analyze command.
- If MCP data appears stale after re-analyzing, restart the MCP client/session.
