/**
 * Helper functions for tooltips
 */
const BrowserText = (function () {
  const canvas = document.createElement("canvas"),
    context = canvas.getContext("2d");

  /**
   * Measures the rendered width of arbitrary text given the font size and font face
   * @param {string} text The text to measure
   * @param {number} fontSize The font size in pixels
   * @param {string} fontFace The font face ("Arial", "Helvetica", etc.)
   **/
  function getWidth(text, fontSize, fontFace) {
    if (context) {
      context.font = fontSize + "px " + fontFace;
      return context.measureText(text).width;
    }
    return 0;
  }

  return {
    getWidth: getWidth,
  };
})();

const determineXFromTitle = (title, x, negativeOffset) => {
  const noWhiteSpaceTitle = title.trim();
  const titleLength =
    noWhiteSpaceTitle.length > 25
      ? `${noWhiteSpaceTitle.slice(0, 26)}...`.length
      : noWhiteSpaceTitle.length;

  return x - negativeOffset - (titleLength * 6) / 2;
};

const determineWidthFromTitle = (title) => {
  const noWhiteSpaceTitle = title.trim();
  const finalTitle =
    noWhiteSpaceTitle.length > 25
      ? `${noWhiteSpaceTitle.slice(0, 26)}...`
      : noWhiteSpaceTitle;

  const width = BrowserText.getWidth(finalTitle, 14, "Arial");
  return width + 4;
};

/**
 * D3 Interactions
 */
class D3Interactions {
  constructor() {
    this.svgWidth = 400;
    this.svgHeight = 400;
    this.d3Connections = {};

    const svg = d3.select("#graph").select("svg");
    svg.call(
      d3.zoom().on("zoom", function (event) {
        svg.selectAll("line").attr("transform", event.transform);
        svg.selectAll("g").attr("transform", event.transform);
      })
    );

    this.d3Link = svg.selectAll("line");
    this.d3Node = svg.selectAll("circle");
    this.d3Simulation = d3
      .forceSimulation() // Apply Force algorithm to data.nodes
      .force(
        "link",
        d3.forceLink().id((node) => node.id)
      ) // pull nodes together based on the links between them
      .force("collide", d3.forceCollide().radius(45)) // add some collision detection so they don't overlap
      .force("center", d3.forceCenter(this.svgWidth / 2, this.svgHeight / 2)) // Draw nodes around the center of the svg area
      .on("tick", () => this.tick(this.d3Node, this.d3Link))
      .on("end", () => this.ended(this.d3Node, this.d3Link));

    this.svg = svg;
    this.nodeColors = d3.scaleOrdinal();

    // Make the height responsive to the screen
    // svgHeight = $refs.svg_container.clientHeight;
    // svgWidth = $refs.svg_container.clientWidth;
  }

  formConnections(links) {
    links.forEach((l) => {
      const key = `${l.source},${l.target}`;
      if (!this.d3Connections[key]) this.d3Connections[key] = true;
    });
  }

  setupColors(node_colors) {
    // Node Colors
    const domain = [];
    const colors = [];
    for (let category_id in node_colors) {
      domain.push(category_id);
      colors.push(node_colors[category_id]);
    }
    this.nodeColors = d3.scaleOrdinal().domain(domain).range(colors);
  }

  formGraph(result) {
    const { links, nodes, node_colors } = result;
    this.formConnections(links);
    this.setupColors(node_colors);

    // Initialize the links
    this.d3Link = this.d3Link.data(links);
    this.d3Link.exit().remove();
    this.d3Link = this.d3Link.enter().append("line").merge(this.d3Link);

    // Initialize the nodes
    this.d3Node = this.d3Node.data(nodes);
    this.d3Node.exit().remove();
    const d3Node = this.d3Node.enter().append("g");

    const circle = d3Node.append("circle").attr("r", 10);

    const tooltipContainer = d3Node.append("svg").attr("height", 20);

    const toolTipBG = tooltipContainer
      .append("rect")
      .attr("x", 0)
      .attr("y", 0)
      .attr("fill", "#303030")
      .attr("fill-opacity", "80%")
      .attr("height", 20);

    const toolTipText = tooltipContainer
      .append("text")
      .attr("fill", "#fff")
      .attr("font-size", "14px")
      .attr("x", "50%")
      .attr("y", 14)
      .attr("text-anchor", "middle")
      .text((d) => {
        const title = d.title.trim();
        return title.length > 25 ? `${title.slice(0, 26)}...` : title;
      });

    this.d3Node = d3Node;

    // Apply changes
    this.d3Simulation.nodes(nodes);
    this.d3Simulation.force("link").links(links);
    this.d3Simulation.alpha(0.01).restart();
  }

  // This function is run at every iteration of the force algorithm
  tick(d3Node, d3Link) {
    // Position links
    d3Link
      .attr("x1", (d) => d.source.x)
      .attr("y1", (d) => d.source.y)
      .attr("x2", (d) => d.target.x)
      .attr("y2", (d) => d.target.y);

    d3Node
      .selectAll("circle")
      .attr("cx", (d) => d.x)
      .attr("cy", (d) => d.y);

    d3Node
      .selectAll("svg")
      .attr("x", (d) => determineXFromTitle(d.title, d.x, 5))
      .attr("y", (d) => d.y - 33)
      .attr("width", (d) => determineWidthFromTitle(d.title));

    d3Node
      .selectAll("rect")
      .attr("width", (d) => determineWidthFromTitle(d.title));

    d3Node.selectAll("svg").style("display", "none");
  }
  ended(d3Node, d3Link) {
    // Add mouse interactions
    d3Node
      .selectAll("circle")
      .attr("data-href", (d) => d.href)
      .attr("fill", (note) => this.nodeColors(note.category))
      .attr("stroke", (note) => this.nodeColors(note.category))
      .attr("id", (note) => note.id)
      .on("mouseover", (e) =>
        this.mouseOver(e, this.isConnected, this.d3Connections, d3Node, d3Link)
      )
      .on("mouseout", (e) => this.mouseOut(d3Node, d3Link))
      .on("click", (e) => this.nodeClick(e, this.isConnected, d3Node, d3Link));
  }

  isConnected(a, b, d3Connections) {
    const first = `${a},${b}`,
      second = `${b},${a}`;
    return d3Connections[first] || d3Connections[second] || a == b;
  }

  // Mouse events!
  // fade nodes on hover
  nodeClick(e) {
    if (e.target.id && e.target.dataset.href && e.metaKey) {
      window.location.href = e.target.dataset.href;
    }
  }

  mouseOver(e, isConnected, d3Connections, d3Node, d3Link) {
    const opacity = 0.2;

    // check all other nodes to see if they're connected
    // to this one. if so, keep the opacity at 1, otherwise 0.2
    d3Node.selectAll("circle").style("fill-opacity", function (o) {
      return isConnected(e.target.id, o.id, d3Connections) ? 1 : opacity;
    });
    d3Node.selectAll("circle").style("stroke-opacity", function (o) {
      return isConnected(e.target.id, o.id, d3Connections) ? 1 : opacity;
    });
    d3Node.selectAll("svg").style("display", function (o) {
      return isConnected(e.target.id, o.id, d3Connections) ? "block" : "none";
    });

    // style link accordingly
    d3Link.style("stroke-opacity", function (o) {
      return o.source.id === e.target.id || o.target.id === e.target.id
        ? 1
        : opacity;
    });
  }

  mouseOut(d3Node, d3Link) {
    d3Node.selectAll("circle").style("fill-opacity", 1);
    d3Node.selectAll("circle").style("stroke-opacity", 1);
    d3Node.selectAll("svg").style("display", "none");

    d3Link.style("stroke-opacity", 1);
    d3Link.style("stroke", "");
  }
}
